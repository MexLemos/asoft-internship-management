<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Renders a view template with layout.
     */
    protected function render(string $view, array $data = [], ?string $layout = 'admin'): Response
    {
        $html = View::render($view, $data, $layout);
        $response = new Response();
        return $response->setContent($html);
    }

    /**
     * Returns a JSON response.
     */
    protected function json(mixed $data, int $status = 200): Response
    {
        return (new Response())->json($data, $status);
    }

    /**
     * Redirects to a specified URL.
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        return (new Response())->redirect($url, $status);
    }

    /**
     * Sets a flash message and redirects.
     */
    protected function redirectWith(string $url, string $flashKey, string $message): Response
    {
        Session::flash($flashKey, $message);
        return $this->redirect($url);
    }

    /**
     * Validates input fields against basic rules.
     * Returns array of error messages indexed by field name.
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && (empty($value) && $value !== '0' && $value !== 0)) {
                    $errors[$field] = "O campo {$field} é obrigatório.";
                    break;
                }

                if (!empty($value)) {
                    if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "O campo {$field} deve conter um email válido.";
                        break;
                    }

                    if (str_starts_with($rule, 'min:')) {
                        $min = (int)substr($rule, 4);
                        if (mb_strlen((string)$value) < $min) {
                            $errors[$field] = "O campo {$field} deve ter no mínimo {$min} caracteres.";
                            break;
                        }
                    }

                    if (str_starts_with($rule, 'max:')) {
                        $max = (int)substr($rule, 4);
                        if (mb_strlen((string)$value) > $max) {
                            $errors[$field] = "O campo {$field} deve ter no máximo {$max} caracteres.";
                            break;
                        }
                    }

                    if ($rule === 'numeric' && !is_numeric($value)) {
                        $errors[$field] = "O campo {$field} deve ser numérico.";
                        break;
                    }
                }
            }
        }

        return $errors;
    }
}
