<?php

namespace App\DTOs;

class IndentDTO
{
    public function __construct(
        public readonly int $projectId,
        public readonly string $indentDate,
        public readonly ?string $requiredByDate,
        public readonly ?string $remarks,
        public readonly array $items,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            projectId:      (int) $data['project_id'],
            indentDate:     $data['indent_date'],
            requiredByDate: $data['required_by_date'] ?? null,
            remarks:        $data['remarks'] ?? null,
            items:          $data['items'] ?? [],
        );
    }
}
