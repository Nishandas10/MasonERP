<?php

namespace App\DTOs;

class ProjectDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $code,
        public readonly ?string $description,
        public readonly ?string $clientName,
        public readonly ?string $clientContact,
        public readonly ?string $location,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly float $contractValue,
        public readonly float $budget,
        public readonly string $status = 'planned',
        public readonly int $progressPercent = 0,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:          $data['name'],
            code:          $data['code'] ?? null,
            description:   $data['description'] ?? null,
            clientName:    $data['client_name'] ?? null,
            clientContact: $data['client_contact'] ?? null,
            location:      $data['location'] ?? null,
            startDate:     $data['start_date'] ?? null,
            endDate:       $data['end_date'] ?? null,
            contractValue: (float) ($data['contract_value'] ?? 0),
            budget:        (float) ($data['budget'] ?? 0),
            status:        $data['status'] ?? 'planned',
            progressPercent: (int) ($data['progress_percent'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'code'           => $this->code,
            'description'    => $this->description,
            'client_name'    => $this->clientName,
            'client_contact' => $this->clientContact,
            'location'       => $this->location,
            'start_date'     => $this->startDate,
            'end_date'       => $this->endDate,
            'contract_value' => $this->contractValue,
            'budget'         => $this->budget,
            'status'         => $this->status,
            'progress_percent' => $this->progressPercent,
        ];
    }
}
