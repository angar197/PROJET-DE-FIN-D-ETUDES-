<?php
// require_once '../includes/auth_check.php';
require_once("../includes/db.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

header("Content-Type: text/plain");

// 🔐 Chargement des variables d’environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$apiKey = $_ENV['GITHUB_TOKEN'] ?? '';

// 🔒 Démarrage de la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔎 Préparation du message utilisateur
$message = trim($_POST['message'] ?? '');
$userMessageLower = strtolower($message);

if (empty($message)) {
    echo "Veuillez poser une question.";
    exit;
}
if (strlen($message) < 2 || strlen($message) > 500) {
    echo "Votre question doit contenir entre 2 et 500 caractères.";
    exit;
}

// 📁 Chargement du fichier JSON
$filepath = __DIR__ . '/../orientation_logic.json';
if (!file_exists($filepath)) {
    echo "Fichier JSON non trouvé.";
    exit;
}

$json = file_get_contents($filepath);
$data = json_decode($json, true);
$intents = $data['intents'] ?? [];

// 🔍 Fonction utilitaire pour détecter un motif dans le message
function matchPattern($message, $patterns) {
    foreach ($patterns as $pattern) {
        if (stripos($message, $pattern) !== false) {
            return $pattern;
        }
    }
    return false;
}



// 🎯 Traitement des intentions
foreach ($intents as $intent) {
    $tag = $intent['tag'] ?? '';
    $patterns = $intent['patterns'] ?? [];

    $matchedPattern = matchPattern($userMessageLower, $patterns);

    if ($matchedPattern !== false) {
        // 🔹 BAC - Type
        if ($tag === 'ask_bac_type') {
            if (preg_match('/scientifique|science|sciences/i', $userMessageLower)) {
                $_SESSION['bac_type'] = "scientifique";
            } elseif (preg_match('/technique/i', $userMessageLower)) {
                $_SESSION['bac_type'] = "technique";
            } elseif (preg_match('/lettre|littéraire/i', $userMessageLower)) {
                $_SESSION['bac_type'] = "littéraire";
            } elseif (preg_match('/éco|économie|economique/i', $userMessageLower)) {
                $_SESSION['bac_type'] = "économique";
            }

            $responses = $intent['responses'] ?? [];
            echo is_array($responses) && count($responses) > 0 ? $responses[0] : "Quel est ton option au bac ?";
            exit;
        }

        // 🔹 BAC - Option
        if ($tag === 'ask_bac_option_details') {
            $_SESSION['bac_option'] = strtoupper($matchedPattern);

            $responses = $intent['responses'] ?? [];

            if (is_array($responses) && isset($responses[$matchedPattern]['text'])) {
                echo $responses[$matchedPattern]['text'];
            } elseif (is_string($responses)) {
                echo $responses;
            } else {
                echo "Merci pour ta réponse.";
            }
            exit;
        }

        // 🔹 Projet professionnel (avec conditions)
        if ($tag === 'ask_project') {
            $responses = $intent['responses'] ?? [];
            $foundResponse = null;

            foreach ($responses as $keyword => $responseData) {
                if (stripos($userMessageLower, strtolower($keyword)) !== false) {
                    $foundResponse = $responseData['text'] ?? null;
                    break;
                }
            }

            if ($foundResponse) {
                echo is_array($foundResponse) ? $foundResponse[0] : $foundResponse;
            } else {
                $default = $intent['default'] ?? "Merci, veux-tu me donner plus de détails sur ton projet ?";
                echo is_array($default) ? $default[0] : $default;
            }
            exit;
        }

        // 🔹 🏫 Informations sur les écoles (ask_school)
        if ($tag === 'ask_school') {
            $responses = $intent['responses'] ?? [];

            $foundSchool = null;

            foreach ($responses as $keyword => $responseData) {
                if (stripos($userMessageLower, strtolower($keyword)) !== false) {
                    $foundSchool = $responseData['text'] ?? null;
                    break;
                }
            }

            if ($foundSchool) {
                echo is_array($foundSchool) ? $foundSchool[0] : $foundSchool;
            } else {
                echo "Peux-tu préciser le nom de l’école ou établissement qui t’intéresse ?";
            }
            exit;
        }

        // 🔹 Réponse générique pour les autres intentions
        $responses = $intent['responses'] ?? ["Merci pour votre message."];
        echo is_array($responses) ? $responses[0] : $responses;
        exit;
    }
}


// 🧩 Recherche dans la base de données si aucun intent ne correspond
$query = $pdo->prepare("
    SELECT r.reponse 
    FROM questions q
    JOIN reponses r ON q.id = r.question_id
    WHERE q.question LIKE ?
    LIMIT 1
");
$query->execute(["%" . $message . "%"]);
$result = $query->fetch();

if ($result) {
    echo htmlspecialchars($result['reponse'], ENT_QUOTES, 'UTF-8');
    exit;
}

// 🤖 Repli : appel à l'API GPT (si aucun match et rien en BDD)
if ($apiKey) {
    $data = [
        "model" => "openai/gpt-4.1",
        "messages" => [
            ["role" => "system", "content" => "Tu es un assistant d’orientation scolaire pour les bacheliers marocains. Réponds de manière  supplémentaires simulant un conseiller d’orientation humain , maximun 2 à 3 phrases "],
            ["role" => "user", "content" => $message]
        ],
        "temperature" => 1,
        "top_p" => 1
    ];

    $options = [
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
            "content" => json_encode($data),
            "ignore_errors" => true
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents('https://models.github.ai/inference/chat/completions', false, $context);

    if ($response !== false && strpos($http_response_header[0], "200") !== false) {
        $responseData = json_decode($response, true);
        $reply = $responseData['choices'][0]['message']['content'] ?? "Je n'ai pas de réponse pour le moment.";

        try {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO questions (question) VALUES (?)")->execute([$message]);
            $questionId = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO reponses (question_id, reponse) VALUES (?, ?)")->execute([$questionId, $reply]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Erreur lors de l'enregistrement.";
            exit;
        }

        echo htmlspecialchars($reply, ENT_QUOTES, 'UTF-8');
        exit;
    }

    echo "Erreur lors de l'appel à l'API GPT.";
    exit;
}

// 🛑 Message final si tout échoue
echo "Je peux seulement répondre aux questions liées à l’orientation scolaire des bacheliers marocains.";
exit;