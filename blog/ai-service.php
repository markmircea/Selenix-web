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
        global $AI_PROMPTS;
        
        $categoryPrompt = isset($AI_PROMPTS['categories'][$category]) 
            ? $AI_PROMPTS['categories'][$category] 
            : $AI_PROMPTS['categories']['tutorials'];
        
        return [
            [
                'role' => 'system',
                'content' => $AI_PROMPTS['system']
            ],
            [
                'role' => 'user',
                'content' => "Write a {$targetWords}-word blog article about: {$topic}

Category: {$category}
Guidelines: {$categoryPrompt}

Requirements:
- Create an engaging, SEO-friendly title
- Write a compelling excerpt (150-160 characters) that makes readers want to click
- Include practical, real-world examples that Selenix.io users can relate to
- Provide actionable tips and best practices readers can implement immediately
- Use proper HTML formatting with headings (h2, h3), paragraphs, and lists
- Write in a conversational but professional tone
- Focus on benefits, outcomes, and practical value
- Include specific use cases for different industries (e-commerce, research, marketing, etc.)
- End with clear next steps or call-to-action

Structure should include:
1. Engaging introduction that hooks the reader
2. 4-6 main sections with descriptive subheadings
3. Practical examples and use cases throughout
4. Actionable tips and best practices
5. Conclusion with next steps

IMPORTANT: Return ONLY valid JSON with NO additional text before or after. Format your response as clean JSON:
{
  \"title\": \"Engaging article title\",
  \"excerpt\": \"Compelling description that drives clicks\",
  \"content\": \"Full HTML content with proper formatting\",
  \"suggestedTags\": [\"tag1\", \"tag2\", \"tag3\"],
  \"readTime\": estimated_minutes,
  \"keyTakeaways\": [\"takeaway1\", \"takeaway2\", \"takeaway3\"]
}"
            ]
        ];
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
            'suggestedTags' => $article['suggestedTags'] ?? ['automation', 'ai-generated'],
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
            $title = 'AI Generated Article';
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
            'suggestedTags' => ['automation', 'ai-generated'],
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