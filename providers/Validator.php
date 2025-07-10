<?php
namespace App\Providers;

class Validator
{
    private array $errors = [];
    private string $field;
    private $value;

    public function field(string $field, $value): self
    {
        $this->field = $field;
        $this->value = trim($value);
        return $this;
    }

    public function required(): self
    {
        if (empty($this->value)) {
            $this->addError("{$this->field} is required.");
        }
        return $this;
    }

    public function min(int $length): self
    {
        if (strlen($this->value) < $length) {
            $this->addError("{$this->field} must be at least {$length} characters long.");
        }
        return $this;
    }

    public function max(int $length): self
    {
        if (strlen($this->value) > $length) {
            $this->addError("{$this->field} cannot exceed {$length} characters.");
        }
        return $this;
    }

    public function email(): self
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->addError("{$this->field} must be a valid email address.");
        }
        return $this;
    }

    public function unique(object $model, string $column): self
    {
        $record = $model->unique($column, $this->value);
        if ($record) {
            $this->addError("{$this->field} is already taken.");
        }
        return $this;
    }

    public function matches(string $otherValue, string $otherFieldName): self
    {
        if ($this->value !== $otherValue) {
            $this->addError("{$this->field} must match {$otherFieldName}.");
        }
        return $this;
    }

    private function addError(string $error): void
    {
        $this->errors[$this->field] = $error;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isSuccess(): bool
    {
        return empty($this->errors);
    }
}