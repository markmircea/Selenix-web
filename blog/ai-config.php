<?php
// AI Configuration for Article Generation
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');
define('AI_MODEL', 'anthropic/claude-3-haiku'); // Cost-effective model for article generation
define('AI_TIMEOUT', 120); // 2 minutes timeout for API calls

// Comprehensive Selenix knowledge base for AI article generation
$SELENIX_KNOWLEDGE = [
    'core_features' => [
        'AI Assistant' => 'Context-aware AI with access to browser HTML, logs, and workspace. Create automations using natural language commands.',
        'Advanced Web Scraping' => 'Extract structured data using JSON configurations with scrapeStructured, scrape collections with scrapeCollection, and monitor elements for changes.',
        'Automated Scheduling' => 'Schedule automations to run hourly, daily, weekly, or monthly with complex recurring patterns and background execution.',
        'Browser State Management' => 'Save and restore complete browser states including cookies, localStorage, and session data using createSnapshot and restoreSnapshot.',
        'Smart Data Export' => 'Export to CSV, JSON with AI-powered data processing, send to APIs via HTTP requests, and integrate with n8n, Zapier, webhooks.',
        'Zero-Code Creation' => 'Visual recording with AI enhancement and natural language automation creation.',
        '100+ Commands' => 'Comprehensive command library with multi-language code export for professional development.'
    ],
    
    'ai_capabilities' => [
        'Natural Language Automation' => 'Describe automations in plain English and AI creates the workflow',
        'Visual Analysis' => 'AI analyzes browser screenshots for troubleshooting and element identification',
        'Smart Element Detection' => 'AI suggests optimal selectors and helps debug locator issues',
        'Intelligent Debugging' => 'AI analyzes logs, errors, and browser state for contextual assistance',
        'Data Processing' => 'sendToAI command processes data with custom instructions like "Filter prices above $100"'
    ],
    
    'scraping_commands' => [
        'scrapeCollection' => 'Extract arrays of similar elements (e.g., all product titles, prices, reviews)',
        'scrapeStructured' => 'Extract complex data structures using JSON field mapping for consistent data',
        'storeText/storeAttribute' => 'Capture individual element data and attributes',
        'monitorElement' => 'Watch elements for changes and react automatically',
        'scrollAndWait' => 'Handle infinite scroll and lazy-loaded content',
        'transformVariable' => 'Apply JavaScript transformations to scraped data'
    ],
    
    'integrations' => [
        'n8n Integration' => 'Send HTTP requests with JSON data and file uploads using multipart/form-data',
        'Zapier Webhooks' => 'Real-time data streaming to 5,000+ apps through webhook integration',
        'API Connections' => 'httpRequest and curlRequest commands for complex API interactions',
        'File Operations' => 'downloadFiles for automatic file downloads, exportToCSV/JSON for data export',
        'CRM Integration' => 'Direct integration with HubSpot, Salesforce, Airtable through API calls',
        'Business Tools' => 'Slack notifications, email alerts, Google Sheets updates, analytics dashboards'
    ],
    
    'professional_features' => [
        'State Snapshots' => 'Save browser sessions including login states to avoid re-authentication',
        'Scheduled Execution' => 'Background automation runs without interrupting work',
        'Error Handling' => 'Smart retry logic and automatic rescheduling on failures',
        'Data Intelligence' => 'AI-powered data analysis and processing capabilities',
        'Enterprise Scale' => 'Handle thousands of URLs with bulk processing and rate limiting',
        'Code Export' => 'Generate code in multiple programming languages for development teams'
    ],
    
    'use_cases' => [
        'E-commerce Monitoring' => 'Track competitor prices, inventory levels, and product catalogs',
        'Lead Generation' => 'Extract contact information and automatically sync to CRM systems',
        'Market Research' => 'Collect industry data, trends, and competitor intelligence',
        'Content Aggregation' => 'Gather articles, reviews, and social media content',
        'Data Migration' => 'Extract data from legacy systems and import to new platforms',
        'Quality Assurance' => 'Automated testing and monitoring of web applications',
        'Real Estate Data' => 'Monitor property listings, prices, and market trends',
        'Job Market Analysis' => 'Track job postings, salary trends, and skill requirements',
        'Social Media Monitoring' => 'Track mentions, sentiment, and engagement metrics',
        'Financial Data Collection' => 'Monitor stock prices, crypto rates, and market indicators'
    ],
    
    'target_users' => [
        'Data Scientists & Analysts' => 'AI-assisted data extraction and automated collection scheduling',
        'Business Intelligence Teams' => 'Automated competitor monitoring and market research workflows',
        'Automation Engineers' => 'Professional-grade automation with 100+ commands and code export',
        'Digital Marketers' => 'Campaign monitoring, competitor analysis, and content collection',
        'Research Professionals' => 'Large-scale data collection and structured information extraction',
        'Operations Teams' => 'Process automation and workflow optimization with AI insights'
    ]
];

// Article generation prompts with comprehensive Selenix knowledge
$AI_PROMPTS = [
    'system' => "You are an expert content writer specializing in Selenix.io, an AI-powered browser automation and web scraping platform. You have deep knowledge of Selenix's capabilities and write engaging, practical articles that help users understand how to leverage the platform's powerful features.

SELENIX KNOWLEDGE BASE:
- AI-Enhanced Browser Automation: Selenix combines AI assistance with 100+ professional commands for intelligent web automation
- Core Capabilities: AI assistant with browser context access, advanced web scraping (scrapeCollection, scrapeStructured), automated scheduling, browser state management (createSnapshot/restoreSnapshot), smart data export (CSV, JSON, HTTP), zero-code creation
- AI Features: Natural language automation creation, visual analysis, smart element detection, intelligent debugging, data processing with sendToAI command
- Professional Integrations: n8n workflows, Zapier webhooks, API connections (httpRequest, curlRequest), CRM systems (HubSpot, Salesforce), business tools (Slack, Google Sheets)
- Advanced Commands: scrapeStructured for complex data extraction, monitorElement for dynamic content, transformVariable for data processing, scrollAndWait for infinite scroll
- Use Cases: E-commerce monitoring, lead generation, market research, competitor analysis, data migration, quality assurance, real estate data, job market analysis
- Target Users: Data scientists, BI teams, automation engineers, digital marketers, research professionals, operations teams

WRITING STYLE:
- Focus on practical business value and real-world applications
- Include specific Selenix commands and examples when relevant
- Emphasize AI-powered capabilities and intelligent automation
- Show clear ROI and efficiency benefits
- Use industry-specific examples and use cases
- Write in a professional but accessible tone
- Include actionable steps users can implement immediately",
    
    'categories' => [
        'tutorials' => 'Write comprehensive step-by-step tutorials showing how to accomplish specific automation goals with Selenix. Focus on practical implementations, include specific commands and examples, and emphasize the AI assistant\'s role in simplifying complex tasks. Show real business value and time savings.',
        
        'features' => 'Write detailed feature explanations that showcase Selenix\'s advanced capabilities. Explain how each feature solves real business problems, provide industry-specific examples, and demonstrate competitive advantages. Include technical details about commands and integrations while remaining accessible.',
        
        'case-studies' => 'Write compelling success stories showing how different industries and professionals use Selenix to solve real business challenges. Include specific metrics, time savings, ROI improvements, and detailed implementation approaches. Focus on transformation and business impact.',
        
        'automation' => 'Write practical automation guides covering best practices, optimization techniques, and advanced strategies. Include AI-powered approaches, professional workflows, integration patterns, and scalability considerations. Emphasize efficiency and intelligent automation.',
        
        'news' => 'Write about industry trends, Selenix updates, and developments in AI-powered automation. Connect broader automation trends to Selenix capabilities and show how the platform addresses emerging business needs.',
        
        'guides' => 'Write comprehensive guides covering broader automation topics, complete workflows, and strategic approaches for different industries. Include multiple Selenix features working together, integration strategies, and enterprise-level implementations.'
    ]
];

// Enhanced article structure optimized for SEO and user engagement
$ARTICLE_STRUCTURE = [
    'min_sections' => 5,
    'max_sections' => 8,
    'min_words' => 1200,
    'max_words' => 2500,
    'include_examples' => true,
    'include_selenix_commands' => true,
    'include_practical_tips' => true,
    'include_roi_metrics' => true,
    'include_integration_examples' => true,
    'focus_on_business_value' => true,
    'seo_optimized' => true
];

// SEO and content optimization guidelines
$CONTENT_GUIDELINES = [
    'seo_focus' => 'Target long-tail keywords related to browser automation, web scraping, AI automation, business process automation, data extraction, and workflow optimization',
    'value_proposition' => 'Always highlight how Selenix saves time, reduces manual work, improves accuracy, and provides competitive advantages',
    'technical_depth' => 'Include specific Selenix commands, integration examples, and code snippets while keeping content accessible',
    'business_context' => 'Frame every feature in terms of business problems it solves and value it delivers',
    'user_journey' => 'Address different skill levels from beginners to automation experts',
    'conversion_focus' => 'Include clear calls-to-action and next steps for readers to try Selenix'
];
?>