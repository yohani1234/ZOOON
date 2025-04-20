<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$formSubmitted = false;
$formError = '';
$formSuccess = '';
$newQuestion = '';
$newEmail = '';
$selectedCategory = '';

$userQuestionsFile = 'user_questions.json';
$adminAnswersFile = 'admin_answers.json';

// Load user questions
$userQuestions = [];
if (file_exists($userQuestionsFile)) {
    $userQuestions = json_decode(file_get_contents($userQuestionsFile), true) ?: [];
}

// Load admin answers (which will be added to FAQs)
$adminAnswers = [];
if (file_exists($adminAnswersFile)) {
    $adminAnswers = json_decode(file_get_contents($adminAnswersFile), true) ?: [];
}

// Virtual Zoo FAQ Categories
$faqCategories = [
    'General' => [
        [
            'question' => 'What is a virtual zoo?',
            'answer' => 'A virtual zoo is an online platform where you can explore and learn about animals and their habitats through interactive content.'
        ],
        [
            'question' => 'Is the virtual zoo free to access?',
            'answer' => 'Yes, our virtual zoo is free to access for everyone.'
        ],
        [
            'question' => 'Can I visit the virtual zoo on my mobile device?',
            'answer' => 'Yes, our virtual zoo is mobile-friendly and can be accessed on any device with an internet connection.'
        ],
        [
            'question' => 'How do I report an issue with the virtual zoo?',
            'answer' => 'You can report issues by contacting us through the "Contact Us" page or emailing support@virtualzoo.com.'
        ],
        [
            'question' => 'Do I need to create an account to use the virtual zoo?',
            'answer' => 'No, you can explore the virtual zoo without an account. However, creating an account allows you to save your progress and access additional features.'
        ]
    ],
    'Animals' => [
        [
            'question' => 'What animals can I see in the virtual zoo?',
            'answer' => 'Our virtual zoo features a wide variety of animals, including lions, tigers, elephants, giraffes, and more.'
        ],
        [
            'question' => 'Are the animals in the virtual zoo real?',
            'answer' => 'No, the animals in the virtual zoo are digital representations designed to educate and entertain visitors.'
        ],
        [
            'question' => 'Can I learn about endangered species in the virtual zoo?',
            'answer' => 'Yes, our virtual zoo has a dedicated section for endangered species, highlighting their habitats and conservation efforts.'
        ],
        [
            'question' => 'How often is the animal information updated?',
            'answer' => 'We update the animal information regularly to ensure accuracy and provide the latest details about their habitats and behaviors.'
        ],
        [
            'question' => 'Can I interact with the animals in the virtual zoo?',
            'answer' => 'Yes, you can interact with the animals through interactive features like 3D models, videos, and quizzes.'
        ]
    ],
    'Tours' => [
        [
            'question' => 'What types of tours are available in the virtual zoo?',
            'answer' => 'We offer guided tours, self-paced tours, and themed tours such as "Rainforest Adventure" and "Savannah Safari."'
        ],
        [
            'question' => 'How do I join a guided tour?',
            'answer' => 'You can join a guided tour by selecting the "Guided Tours" option on the homepage and choosing a time slot.'
        ],
        [
            'question' => 'Are the tours available in multiple languages?',
            'answer' => 'Yes, our tours are available in multiple languages. You can select your preferred language in the settings.'
        ],
        [
            'question' => 'Can I customize my tour experience?',
            'answer' => 'Yes, you can customize your tour by selecting specific animals or habitats you want to explore.'
        ],
        [
            'question' => 'Is there a fee for guided tours?',
            'answer' => 'No, all tours in the virtual zoo are free of charge.'
        ]
    ],
    'Education' => [
        [
            'question' => 'Does the virtual zoo offer educational resources?',
            'answer' => 'Yes, we provide educational resources such as videos, articles, and quizzes to help you learn about animals and their habitats.'
        ],
        [
            'question' => 'Can schools use the virtual zoo for educational purposes?',
            'answer' => 'Yes, schools can use our virtual zoo as a learning tool. We also offer special features for educators.'
        ],
        [
            'question' => 'Are there activities for children in the virtual zoo?',
            'answer' => 'Yes, we have interactive activities and games designed specifically for children to make learning fun.'
        ],
        [
            'question' => 'Can I download educational materials from the virtual zoo?',
            'answer' => 'Yes, you can download PDFs, worksheets, and other materials from the "Resources" section.'
        ],
        [
            'question' => 'Does the virtual zoo host live educational events?',
            'answer' => 'Yes, we host live webinars and Q&A sessions with animal experts. Check the "Events" section for upcoming sessions.'
        ]
    ],
    'Technical Support' => [
        [
            'question' => 'What should I do if the virtual zoo is not loading?',
            'answer' => 'Ensure you have a stable internet connection and try refreshing the page. If the issue persists, contact support.'
        ],
        [
            'question' => 'Can I access the virtual zoo on any browser?',
            'answer' => 'Yes, the virtual zoo is compatible with all modern browsers, including Chrome, Firefox, Safari, and Edge.'
        ],
        [
            'question' => 'How do I reset my account password?',
            'answer' => 'You can reset your password by clicking on "Forgot Password" on the login page and following the instructions.'
        ],
        [
            'question' => 'What should I do if I encounter a bug?',
            'answer' => 'Please report bugs through the "Contact Us" page or email us at support@virtualzoo.com.'
        ],
        [
            'question' => 'Is there a mobile app for the virtual zoo?',
            'answer' => 'Yes, our mobile app is available for download on both iOS and Android platforms.'
        ]
    ]
];

// Add admin answers to FAQ categories
foreach ($adminAnswers as $answer) {
    if (isset($faqCategories[$answer['category']])) {
        $faqCategories[$answer['category']][] = [
            'question' => $answer['question'],
            'answer' => $answer['answer']
        ];
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['question'])) {
            throw new Exception('Question is required');
        }
        
        if (empty($_POST['email'])) {
            throw new Exception('Email is required');
        }

        $newQuestion = htmlspecialchars(trim($_POST['question']));
        $newEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $selectedCategory = htmlspecialchars($_POST['category'] ?? '');

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }

        if (!isset($faqCategories[$selectedCategory])) {
            $selectedCategory = 'General';
        }
        
        $userQuestions[] = [
            'question' => $newQuestion,
            'category' => $selectedCategory,
            'email' => $newEmail,
            'date' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'answer' => ''
        ];
        
        // Save to JSON file
        file_put_contents($userQuestionsFile, json_encode($userQuestions, JSON_PRETTY_PRINT));
        
        $formSubmitted = true;
        $formSuccess = 'Thank you for your question! Our team will review it and get back to you.';
        
        // Reset form fields
        $newQuestion = '';
        $newEmail = '';
    } catch (Exception $e) {
        $formError = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-green-400 to-green-700 min-h-screen text-white">
   
    <nav class="bg-gray-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">JUNGLE SAFARII</h1>
            <div class="space-x-4">
                <a href="admin.php" class="relative inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium rounded-lg group bg-gradient-to-br from-yellow-400 to-green-500 hover:from-yellow-500 hover:to-green-600 text-black shadow-lg transform transition-all duration-300 hover:scale-105">
                    Admin Panel
                </a>
                <a href="index.html" class="relative inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium rounded-lg group bg-gradient-to-br from-yellow-400 to-green-600 hover:from-yellow-500 hover:to-green-600 text-black shadow-lg transform transition-all duration-300 hover:scale-105">
                    Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto py-8 px-4 max-w-6xl">
        <h1 class="text-3xl font-bold text-center mb-8 text-white">FAQs</h1>

        <!-- Category Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach (array_keys($faqCategories) as $index => $category): ?>
                <button onclick="showCategory('<?php echo htmlspecialchars($category); ?>')" 
                        class="category-tab px-4 py-2 rounded-lg border border-white text-black hover:bg-white hover:text-blue-600 transition <?php echo $index === 0 ? 'bg-white text-blue-600' : ''; ?>">
                    <?php echo htmlspecialchars($category); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- FAQ Categories -->
        <?php foreach ($faqCategories as $category => $faqs): ?>
            <div id="category-<?php echo htmlspecialchars($category); ?>" class="faq-category mb-8 <?php echo $category !== array_key_first($faqCategories) ? 'hidden' : ''; ?>">
                <h2 class="text-2xl font-bold mb-4 text-white"><?php echo htmlspecialchars($category); ?> Questions</h2>
                <div class="bg-gradient-to-r from-yellow-200 to-green-400 rounded-lg shadow-md overflow-hidden text-gray-900">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="faq-item border-b border-gray-600 last:border-b-0">
                            <div class="faq-question flex justify-between items-center p-4 cursor-pointer hover:bg-gray-600/40 hover:text-gray-900 transition-colors duration-200" 
                                 onclick="toggleAnswer('<?php echo htmlspecialchars($category); ?>', <?php echo $index; ?>)">
                                <h3 class="font-bold"><?php echo htmlspecialchars($faq['question']); ?></h3>
                                <span id="icon-<?php echo htmlspecialchars($category); ?>-<?php echo $index; ?>" class="text-xl font-light">+</span>
                            </div>
                            <div id="answer-<?php echo htmlspecialchars($category); ?>-<?php echo $index; ?>" class="faq-answer hidden px-4 pb-4 pt-2 bg-white/60 text-gray-900">
                                <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Ask a Question Section -->
        <div class="bg-gradient-to-r from-yellow-200 to-green-400 rounded-lg shadow-md p-6 mt-8 mb-10 text-gray-900">
            <h2 class="text-2xl font-bold mb-4">Ask a Question</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label for="category" class="block font-medium mb-2">Category*</label>
                    <select id="category" name="category" class="w-full px-3 py-2 bg-white/70 border rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                        <?php foreach (array_keys($faqCategories) as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="question" class="block font-medium mb-2">Your Question*</label>
                    <textarea id="question" name="question" rows="3" 
                              class="w-full px-3 py-2 border bg-white/70 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required></textarea>
                </div>
                <div>
                    <label for="email" class="block font-medium mb-2">Your Email*</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-3 py-2 border bg-white/70 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           required>
                </div>
                <button type="submit" class="w-full font-bold bg-white/70 text-black py-2 px-4 border-2 border-black rounded-lg hover:bg-gray-500 hover:text-white transition">
                    Submit Question
                </button>
            </form>
        </div>
    </div>

    <script>
        // Show selected category
        function showCategory(category) {
            document.querySelectorAll('.faq-category').forEach(el => {
                el.classList.add('hidden');
            });
            document.getElementById('category-' + category).classList.remove('hidden');
            
            // Update active tab styling
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.classList.remove('bg-white', 'text-blue-600');
            });
            event.target.classList.add('bg-white', 'text-blue-600');
        }

        function toggleAnswer(category, index) {
            const answer = document.getElementById('answer-' + category + '-' + index);
            const icon = document.getElementById('icon-' + category + '-' + index);
            
            answer.classList.toggle('hidden');
            
            if (answer.classList.contains('hidden')) {
                icon.textContent = '+';
            } else {
                icon.textContent = '−';
            }
        }
    </script>

</body>
</html>