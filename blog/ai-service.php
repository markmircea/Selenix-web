<?php
require_once 'ai-config.php';

class AIService {
    private $apiKey;
    
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey;
    }
    
    public function generateArticle($topic, $category = 'tutorials', $targetWords = 1500) {
        if (!$this->apiKey) {
            throw new Exception('OpenRouter API key not configured');
        }
        
        global $AI_PROMPTS;
        
        $prompt = $this->buildPrompt($topic, $category, $targetWords);
        
        $response = $this->callOpenRouter($prompt);
        
        if (!$response) {
            throw new Exception('Failed to generate article');
        }
        
        return $this->parseArticleResponse($response);
    }
    
    private function buildPrompt($topic, $category, $targetWords) {
        global $AI_PROMPTS, $SELENIX_KNOWLEDGE, $ARTICLE_STRUCTURE, $CONTENT_GUIDELINES;
        
        $categoryPrompt = isset($AI_PROMPTS['categories'][$category]) 
            ? $AI_PROMPTS['categories'][$category] 
            : $AI_PROMPTS['categories']['tutorials'];
        
        // Build enhanced prompt with Selenix knowledge
        $selenixContext = $this->buildSelenixContext();
        $seoKeywords = $this->generateSeoKeywords($topic, $category);
        
        return [
            [
                'role' => 'system',
                'content' => $AI_PROMPTS['system'] . "\n\n" . $selenixContext
            ],
            [
                'role' => 'user',
                'content' => "Write a {$targetWords}-word SEO-optimized blog article about: {$topic}

Category: {$category}
Content Focus: {$categoryPrompt}

SEO OPTIMIZATION:
Target Keywords: {$seoKeywords}
Content Goals: Drive organic traffic, generate leads, showcase Selenix capabilities
User Intent: Help users understand how Selenix solves their automation challenges

CONTENT REQUIREMENTS:
- Create an engaging, SEO-friendly title that includes primary keywords
- Write a compelling meta description (150-160 characters) that drives clicks
- Include specific Selenix commands, features, and capabilities throughout
- Provide real-world business examples and use cases
- Show clear ROI and efficiency benefits with specific metrics when possible
- Include step-by-step implementation guidance
- Add practical tips users can implement immediately
- Demonstrate AI-powered automation advantages
- Include integration examples (n8n, Zapier, APIs, CRMs)
- Use proper HTML formatting with descriptive headings (h2, h3)
- Write in a professional but accessible tone
- Focus on business value and practical outcomes

STRUCTURE REQUIREMENTS:
1. Hook introduction that identifies a common business problem
2. 5-7 main sections with SEO-optimized subheadings
3. Specific Selenix examples and command demonstrations
4. Real-world implementation scenarios
5. Integration and workflow examples
6. ROI and efficiency benefits
7. Actionable next steps and clear call-to-action

IMPORTANT: Return ONLY valid JSON with NO additional text. Format:
{
  \"title\": \"SEO-optimized article title with keywords\",
  \"excerpt\": \"Compelling meta description that drives clicks (150-160 chars)\",
  \"content\": \"Full HTML content with proper formatting and Selenix examples\",
  \"suggestedTags\": [\"selenix\", \"automation\", \"web-scraping\", \"ai\", \"workflow\"],
  \"readTime\": estimated_minutes,
  \"keyTakeaways\": [\"takeaway1\", \"takeaway2\", \"takeaway3\"]
}"
            ]
        ];
    }
    
    private function buildSelenixContext() {
        global $SELENIX_KNOWLEDGE;
        
        $context = "DETAILED SELENIX CAPABILITIES:\n\n";
        
        $context .= "CORE FEATURES:\n";
        foreach ($SELENIX_KNOWLEDGE['core_features'] as $feature => $description) {
            $context .= "- {$feature}: {$description}\n";
        }
        
        $context .= "\nAI-POWERED CAPABILITIES:\n";
        foreach ($SELENIX_KNOWLEDGE['ai_capabilities'] as $capability => $description) {
            $context .= "- {$capability}: {$description}\n";
        }
        
        $context .= "\nKEY COMMANDS & EXAMPLES:\n";
        foreach ($SELENIX_KNOWLEDGE['scraping_commands'] as $command => $description) {
            $context .= "- {$command}: {$description}\n";
        }
        
        $context .= "\nINTEGRATION CAPABILITIES:\n";
        foreach ($SELENIX_KNOWLEDGE['integrations'] as $integration => $description) {
            $context .= "- {$integration}: {$description}\n";
        }
        
        $context .= "\nTARGET USERS & USE CASES:\n";
        foreach ($SELENIX_KNOWLEDGE['target_users'] as $user => $description) {
            $context .= "- {$user}: {$description}\n";
        }
        
        return $context;
    }
    
    private function generateSeoKeywords($topic, $category) {
        $baseKeywords = [
            'browser automation',
            'web scraping',
            'AI automation',
            'selenix',
            'workflow automation',
            'data extraction',
            'automated data collection'
        ];
        
        $categoryKeywords = [
            'tutorials' => ['how to automate', 'step by step guide', 'automation tutorial', 'web scraping guide'],
            'features' => ['automation features', 'scraping capabilities', 'AI assistant', 'smart automation'],
            'case-studies' => ['automation success', 'business case study', 'ROI automation', 'efficiency gains'],
            'automation' => ['best practices', 'automation strategy', 'workflow optimization', 'process automation'],
            'news' => ['automation trends', 'industry news', 'AI developments', 'technology updates'],
            'guides' => ['comprehensive guide', 'complete workflow', 'enterprise automation', 'advanced techniques']
        ];
        
        $keywords = array_merge($baseKeywords, $categoryKeywords[$category] ?? []);
        
        // Add topic-specific keywords based on content
        if (stripos($topic, 'e-commerce') !== false || stripos($topic, 'product') !== false) {
            $keywords = array_merge($keywords, ['e-commerce automation', 'product data scraping', 'price monitoring']);
        }
        if (stripos($topic, 'lead') !== false || stripos($topic, 'crm') !== false) {
            $keywords = array_merge($keywords, ['lead generation', 'CRM automation', 'contact extraction']);
        }
        if (stripos($topic, 'social') !== false || stripos($topic, 'media') !== false) {
            $keywords = array_merge($keywords, ['social media automation', 'content monitoring', 'engagement tracking']);
        }
        if (stripos($topic, 'market') !== false || stripos($topic, 'research') !== false) {
            $keywords = array_merge($keywords, ['market research automation', 'competitor analysis', 'data intelligence']);
        }
        
        return implode(', ', array_unique($keywords));
    }
    
    private function callOpenRouter($messages) {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: https://selenix.io',
            'X-Title: Selenix Blog AI Generator'
        ];
        
        $data = [
            'model' => AI_MODEL,
            'messages' => $messages,
            'max_tokens' => 4000,
            'temperature' => 0.7,
            'top_p' => 0.9
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, OPENROUTER_API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, AI_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API error: HTTP ' . $httpCode . ' - ' . $response);
        }
        
        $decoded = json_decode($response, true);
        
        if (!$decoded || !isset($decoded['choices'][0]['message']['content'])) {
            throw new Exception('Invalid API response format');
        }
        
        return $decoded['choices'][0]['message']['content'];
    }
    
    private function parseArticleResponse($response) {
        // Log the raw response for debugging
        error_log('AI Raw Response: ' . substr($response, 0, 500) . '...');
        
        // Clean the response - remove any non-JSON content
        $response = trim($response);
        
        // Remove markdown code blocks if present
        $response = preg_replace('/^```json\s*/', '', $response);
        $response = preg_replace('/\s*```$/', '', $response);
        
        // Try to find JSON boundaries more accurately
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            
            // Clean any escaped quotes that might be causing issues
            $jsonString = $this->cleanJsonString($jsonString);
            
            // Log cleaned JSON for debugging
            error_log('Cleaned JSON: ' . substr($jsonString, 0, 500) . '...');
            
            $article = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($article['title'], $article['content'])) {
                // Ensure content is properly formatted
                $article = $this->processArticleContent($article);
                return $article;
            } else {
                error_log('JSON Decode Error: ' . json_last_error_msg());
                error_log('Failed JSON: ' . substr($jsonString, 0, 500) . '...');
            }
        }
        
        // If JSON parsing fails, try manual parsing
        error_log('JSON parsing failed, trying manual parsing');
        return $this->manualParseResponse($response);
    }
    
    private function cleanJsonString($jsonString) {
        // Fix common JSON issues
        // Replace HTML entities
        $jsonString = html_entity_decode($jsonString, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Fix escaped quotes issues
        $jsonString = str_replace('\\"', '"', $jsonString);
        $jsonString = str_replace('\\\\', '\\', $jsonString);
        
        // Remove any BOM or invisible characters
        $jsonString = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $jsonString);
        
        return $jsonString;
    }
    
    private function processArticleContent($article) {
        // Ensure all fields exist with defaults
        $processedArticle = [
            'title' => $article['title'] ?? 'AI Generated Article',
            'excerpt' => $article['excerpt'] ?? '',
            'content' => $article['content'] ?? '',
            'suggestedTags' => $article['suggestedTags'] ?? ['selenix', 'automation', 'ai-powered'],
            'readTime' => $article['readTime'] ?? $this->calculateReadTime($article['content'] ?? ''),
            'keyTakeaways' => $article['keyTakeaways'] ?? []
        ];
        
        // Clean and validate content
        $processedArticle['content'] = $this->sanitizeHtmlContent($processedArticle['content']);
        
        // Generate excerpt if empty
        if (empty($processedArticle['excerpt'])) {
            $processedArticle['excerpt'] = $this->generateExcerpt($processedArticle['content']);
        }
        
        // Calculate read time if not provided or invalid
        if (!is_numeric($processedArticle['readTime']) || $processedArticle['readTime'] < 1) {
            $processedArticle['readTime'] = $this->calculateReadTime($processedArticle['content']);
        }
        
        return $processedArticle;
    }
    
    private function sanitizeHtmlContent($content) {
        // Allow specific HTML tags for blog content
        $allowedTags = '<p><br><strong><b><em><i><u><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><img><code><pre>';
        
        // Clean the content but preserve allowed HTML
        $content = strip_tags($content, $allowedTags);
        
        // Ensure proper paragraph formatting
        $content = $this->wpautop($content);
        
        return $content;
    }
    
    private function generateExcerpt($content, $length = 160) {
        $text = strip_tags($content);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $truncated = substr($text, 0, $length);
        $lastSpace = strrpos($truncated, ' ');
        
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return $truncated . '...';
    }
    
    private function calculateReadTime($content) {
        $wordCount = str_word_count(strip_tags($content));
        $averageWordsPerMinute = 200;
        $minutes = ceil($wordCount / $averageWordsPerMinute);
        
        return max(1, $minutes);
    }
    
    private function manualParseResponse($response) {
        // Enhanced manual parsing as fallback
        $lines = explode("\n", $response);
        $title = '';
        $content = '';
        
        // Try to extract title from various formats
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^#+\s+(.+)/', $line, $matches)) {
                $title = $matches[1];
                break;
            } elseif (preg_match('/^title[:\s]+(.+)/i', $line, $matches)) {
                $title = trim($matches[1], '"\'');
                break;
            }
        }
        
        if (!$title) {
            $title = 'AI Generated Selenix Article';
        }
        
        // Clean up the content
        $content = $response;
        
        // Remove any JSON fragments
        $content = preg_replace('/\{[^}]*"title"[^}]*\}/', '', $content);
        
        // Convert markdown-style headers to HTML
        $content = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $content);
        $content = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $content);
        
        // Convert line breaks to paragraphs
        $content = $this->wpautop($content);
        
        return [
            'title' => $title,
            'excerpt' => $this->generateExcerpt($content),
            'content' => $content,
            'suggestedTags' => ['selenix', 'automation', 'ai-powered', 'web-scraping'],
            'readTime' => $this->calculateReadTime($content),
            'keyTakeaways' => []
        ];
    }
    
    // WordPress-style autop function for converting line breaks to paragraphs
    private function wpautop($pee, $br = true) {
        $pre_tags = array();
        
        if (trim($pee) === '') {
            return '';
        }
        
        $pee = $pee . "\n";
        
        if (strpos($pee, '<pre') !== false) {
            $pee_parts = explode('</pre>', $pee);
            $last_pee = array_pop($pee_parts);
            $pee = '';
            $i = 0;
            
            foreach ($pee_parts as $pee_part) {
                $start = strpos($pee_part, '<pre');
                
                if ($start === false) {
                    $pee .= $pee_part;
                    continue;
                }
                
                $name = "<pre wp-pre-tag-$i></pre>";
                $pre_tags[$name] = substr($pee_part, $start) . '</pre>';
                
                $pee .= substr($pee_part, 0, $start) . $name;
                $i++;
            }
            
            $pee .= $last_pee;
        }
        
        $pee = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee);
        
        $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
        
        $pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n\n$1", $pee);
        $pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
        $pee = str_replace(array("\r\n", "\r"), "\n", $pee);
        
        if (strpos($pee, '<object') !== false) {
            $pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee);
            $pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
        }
        
        $pee = preg_replace("/\n\n+/", "\n\n", $pee);
        $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
        $pee = '';
        
        foreach ($pees as $tinkle) {
            $pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
        }
        
        $pee = preg_replace('|<p>\s*</p>|', '', $pee);
        $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);
        $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
        $pee = preg_replace("!<p>(<li.+?)</p>!", "$1", $pee);
        $pee = preg_replace('!<p><blockquote([^>]*)>!i', "<blockquote$1><p>", $pee);
        $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
        $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
        
        if ($br) {
            $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', function($matches) {
                return str_replace("\n", "<WPPreserveNewline />", $matches[0]);
            }, $pee);
            $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);
            $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
        }
        
        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
        $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
        $pee = preg_replace("|\n</p>$|", '</p>', $pee);
        
        if (!empty($pre_tags)) {
            $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);
        }
        
        return $pee;
    }
}
?>