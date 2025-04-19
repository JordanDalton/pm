<?php

namespace App\Services;

use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use App\Models\Board;
use App\Models\Task;

class AIAssistantService
{
    /**
     * Generate insights for a board
     */
    public function generateBoardInsights(Board $board): string
    {
        $tasks = $board->tasks()->with('user')->get();
        
        $boardData = [
            'name' => $board->name,
            'description' => $board->description,
            'tasks' => $tasks->map(function($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'assignee' => $task->user ? $task->user->name : 'Unassigned',
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                ];
            })
        ];
        
        $prompt = <<<PROMPT
        You are an AI project management assistant. Analyze the following board data and provide 3 insightful observations 
        and actionable recommendations to help the team be more productive.

        Board: {$board->name}
        Description: {$board->description}
        
        Tasks:
        
        PROMPT;
        
        foreach ($tasks as $task) {
            $prompt .= "- {$task->title} (Status: {$task->status}, Priority: {$task->priority}, ";
            $prompt .= "Assignee: " . ($task->user ? $task->user->name : 'Unassigned');
            if ($task->due_date) {
                $prompt .= ", Due: " . $task->due_date->format('Y-m-d');
            }
            $prompt .= ")\n";
        }
        
        $prompt .= <<<PROMPT
        
        Provide your analysis in this format:
        
        ## Insights
        1. [First insight about the project status]
        2. [Second insight about workload distribution]
        3. [Third insight about priorities or deadlines]
        
        ## Recommendations
        1. [First actionable recommendation]
        2. [Second actionable recommendation]
        3. [Third actionable recommendation]
        
        Keep your response concise and focused on helping the team improve their productivity and project success.
        PROMPT;
        
        try {
            $response = Prism::text()
                ->using(Provider::Anthropic, 'claude-3-haiku')
                ->withPrompt($prompt)
                ->asText();
                
            return $response;
        } catch (\Exception $e) {
            return "Sorry, I couldn't generate insights at this time. Please try again later.";
        }
    }
    
    /**
     * Generate a suggested task priority
     */
    public function suggestTaskPriority(string $title, string $description): string
    {
        $prompt = <<<PROMPT
        You are an AI project management assistant. Based on the following task title and description, 
        suggest an appropriate priority level (Low, Medium, or High).
        
        Task Title: $title
        
        Task Description: $description
        
        Consider the following factors:
        - Urgency of language in the description
        - Complexity of the task
        - Dependencies implied in the description
        - Business impact
        
        Return only the priority level as a single word: "Low", "Medium", or "High".
        PROMPT;
        
        try {
            $response = Prism::text()
                ->using(Provider::Anthropic, 'claude-3-haiku')
                ->withPrompt($prompt)
                ->asText();
                
            // Clean up response to ensure we get just the priority
            $response = trim($response);
            
            // Validate response is one of the expected values
            if (!in_array($response, ['Low', 'Medium', 'High'])) {
                return 'Medium'; // Default to Medium if response is unexpected
            }
            
            return $response;
        } catch (\Exception $e) {
            return 'Medium'; // Default to Medium on error
        }
    }
    
    /**
     * Enhance a task description
     */
    public function enhanceTaskDescription(string $title, string $description = ''): string
    {
        if (empty($description)) {
            $prompt = <<<PROMPT
            You are an AI project management assistant. Create a detailed, professional task description for the following task title.
            
            Task Title: $title
            
            The description should:
            - Be 2-4 sentences long
            - Clearly explain what needs to be done
            - Include any implied requirements
            - Use professional language
            
            Provide only the description text, no additional commentary.
            PROMPT;
        } else {
            $prompt = <<<PROMPT
            You are an AI project management assistant. Enhance the following task description to be more detailed and clear.
            
            Task Title: $title
            
            Current Description: $description
            
            The enhanced description should:
            - Be 2-4 sentences long
            - Clearly explain what needs to be done
            - Include any implied requirements
            - Use professional language
            - Preserve the original intent but add clarity and detail
            
            Provide only the enhanced description text, no additional commentary.
            PROMPT;
        }
        
        try {
            $response = Prism::text()
                ->using(Provider::Anthropic, 'claude-3-haiku')
                ->withPrompt($prompt)
                ->asText();
                
            return $response;
        } catch (\Exception $e) {
            return $description ?: "No description provided. Please add details about this task.";
        }
    }
}