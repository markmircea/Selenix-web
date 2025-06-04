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

Format your response as JSON with these fields:
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
        // Try to extract JSON from the response
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $article = json_decode($jsonString, true);
            
            if ($article && isset($article['title'], $article['content'])) {
                return $article;
            }
        }
        
        // Fallback: parse manually if JSON parsing fails
        return $this->manualParseResponse($response);
    }
    
    private function manualParseResponse($response) {
        // Basic parsing fallback
        $lines = explode("\n", $response);
        $title = '';
        $excerpt = '';
        $content = $response;
        
        // Try to extract title from first line or heading
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^#\s+(.+)/', $line, $matches)) {
                $title = $matches[1];
                break;
            }
        }
        
        if (!$title) {
            $title = 'AI Generated Article';
        }
        
        // Generate basic excerpt
        $excerpt = truncateText(strip_tags($content), 150);
        
        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'suggestedTags' => ['automation', 'ai-generated'],
            'readTime' => max(1, round(str_word_count($content) / 200)),
            'keyTakeaways' => []
        ];
    }
}
?>
