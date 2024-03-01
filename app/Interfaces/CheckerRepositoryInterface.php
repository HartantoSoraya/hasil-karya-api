<?php

namespace App\Interfaces;

interface CheckerRepositoryInterface
{
    public function getAllCheckers();

    public function create(array $data);

    public function getCheckerById(string $id);

    public function update(array $data, string $id);

    public function delete(string $id);

    public function generateCode(int $tryCount): string;

    public function isUniqueCode(string $code, $exceptId = null): bool;
}
