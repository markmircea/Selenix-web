<?php
// AI Configuration for Article Generation
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');
define('AI_MODEL', 'anthropic/claude-3-haiku'); // Cost-effective model for article generation
define('AI_TIMEOUT', 120); // 2 minutes timeout for API calls

// Article generation prompts
$AI_PROMPTS = [
    'system' => "You are an expert content writer for Selenix.io, an AI-powered browser automation and web scraping platform. Write engaging, informative blog articles that help business professionals, data analysts, marketers, and automation enthusiasts understand and leverage browser automation tools. Focus on practical value, real-world applications, and actionable insights rather than technical implementation details.",
    
    'categories' => [
        'tutorials' => 'Write step-by-step tutorials that guide users through accomplishing specific automation goals, focusing on the what and why rather than technical how-to details.',
        'features' => 'Write about automation features and capabilities, explaining their business value and practical applications with real-world examples.',
        'case-studies' => 'Write compelling case studies showing how automation solved real business problems, improved efficiency, or delivered measurable results.',
        'automation' => 'Write practical tips, best practices, and strategies for browser automation and web scraping that readers can immediately apply.',
        'news' => 'Write about industry trends, updates, and developments in the automation and AI space that impact business users.',
        'guides' => 'Write comprehensive guides covering broader automation topics, workflows, and strategies for different industries or use cases.'
    ]
];

// Article structure template
$ARTICLE_STRUCTURE = [
    'min_sections' => 4,
    'max_sections' => 7,
    'min_words' => 1000,
    'max_words' => 2000,
    'include_examples' => true,
    'include_practical_tips' => true,
    'include_actionable_advice' => true,
    'focus_on_benefits' => true
];
?>
