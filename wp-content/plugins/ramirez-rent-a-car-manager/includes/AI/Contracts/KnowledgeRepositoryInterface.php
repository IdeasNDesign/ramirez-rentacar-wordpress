<?php
namespace RamirezRentACar\AI\Contracts;

interface KnowledgeRepositoryInterface {
	public function query(string $text, string $type = '', array $options = []): array;
	public function add(string $title, string $content, string $type, array $keywords = []);
}
