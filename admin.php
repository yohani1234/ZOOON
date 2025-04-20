<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$userQuestionsFile = 'user_questions.json';
$adminAnswersFile = 'admin_answers.json';

// Load user questions
$userQuestions = [];
if (file_exists($userQuestionsFile)) {
    $userQuestions = json_decode(file_get_contents($userQuestionsFile), true) ?: [];
}

// Load admin answers
$adminAnswers = [];
if (file_exists($adminAnswersFile)) {
    $adminAnswers = json_decode(file_get_contents($adminAnswersFile), true) ?: [];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['answer_question'])) {
        $questionId = $_POST['question_id'];
        $answer = trim($_POST['answer']);
        
        if (!empty($answer) && isset($userQuestions[$questionId])) {
            // Update the question with answer
            $userQuestions[$questionId]['answer'] = $answer;
            $userQuestions[$questionId]['status'] = 'answered';
            $userQuestions[$questionId]['answered_by'] = 'Admin';
            $userQuestions[$questionId]['answer_date'] = date('Y-m-d H:i:s');
            
            // Add to admin answers for FAQ
            $adminAnswers[] = [
                'question' => $userQuestions[$questionId]['question'],
                'answer' => $answer,
                'category' => $userQuestions[$questionId]['category'],
                'date_added' => date('Y-m-d H:i:s')
            ];
            
            // Save to files
            file_put_contents($userQuestionsFile, json_encode($userQuestions, JSON_PRETTY_PRINT));
            file_put_contents($adminAnswersFile, json_encode($adminAnswers, JSON_PRETTY_PRINT));
            
            $_SESSION['message'] = 'Answer submitted successfully!';
            header('Location: admin.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Programming FAQs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-yellow-300 to-green-500 min-h-screen">
   
    <nav class="bg-green-700 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Admin Panel</h1>
            <div class="space-x-4">
                <a href="faq.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100">View FAQs</a>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-400">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto py-8 px-4 max-w-6xl">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <h1 class="text-3xl font-bold text-center mb-8 text-blue-600">Pending Questions</h1>
        
        <div class="bg-white/70 rounded-lg shadow-md overflow-hidden">
            <?php if (empty($userQuestions)): ?>
                <div class="p-4 text-center text-gray-500">No questions pending.</div>
            <?php else: ?>
                <?php foreach ($userQuestions as $id => $question): ?>
                    <?php if ($question['status'] === 'pending'): ?>
                        <div class="border-b border-gray-200 p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($question['question']); ?></h3>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Category:</span> <?php echo htmlspecialchars($question['category']); ?> | 
                                        <span class="font-medium">From:</span> <?php echo htmlspecialchars($question['email']); ?> | 
                                        <span class="font-medium">Date:</span> <?php echo htmlspecialchars($question['date']); ?>
                                    </div>
                                </div>
                                <button onclick="toggleAnswerForm('form-<?php echo $id; ?>')" 
                                        class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                    Answer
                                </button>
                            </div>
                            
                            <div id="form-<?php echo $id; ?>" class="hidden mt-3">
                                <form method="POST">
                                    <input type="hidden" name="question_id" value="<?php echo $id; ?>">
                                    <div class="mb-3">
                                        <label class="block text-gray-700 font-medium mb-1">Your Answer</label>
                                        <textarea name="answer" rows="4" class="w-full bg-white/90 px-3 py-2 border border-gray-300 rounded-lg" required></textarea>
                                    </div>
                                    <button type="submit" name="answer_question" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                        Submit Answer
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <h1 class="text-3xl font-bold text-center my-8 text-blue-600">Answered Questions</h1>
        
        <div class="bg-white/80 rounded-lg shadow-md overflow-hidden mb-8">
            <?php foreach ($userQuestions as $id => $question): ?>
                <?php if ($question['status'] === 'answered'): ?>
                    <div class="border-b border-gray-200 p-4">
                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($question['question']); ?></h3>
                        <div class="text-sm text-gray-600 mt-1">
                            <span class="font-medium">Category:</span> <?php echo htmlspecialchars($question['category']); ?> | 
                            <span class="font-medium">From:</span> <?php echo htmlspecialchars($question['email']); ?> | 
                            <span class="font-medium">Date:</span> <?php echo htmlspecialchars($question['date']); ?> |
                            <span class="font-medium">Answered on:</span> <?php echo htmlspecialchars($question['answer_date']); ?>
                        </div>
                        <div class="mt-2 p-3 bg-blue-50 border-2 border-gray-400 rounded">
                            <p class="font-medium">Answer:</p>
                            <p><?php echo htmlspecialchars($question['answer']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleAnswerForm(formId) {
            const form = document.getElementById(formId);
            form.classList.toggle('hidden');
        }
    </script>
</body>
</html>