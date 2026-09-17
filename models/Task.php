<?php

class Task
{
    public const VALID_STATUSES = ['Pending', 'Completed'];
    public const VALID_PRIORITIES = ['Low', 'Medium', 'High'];

    public static function validatePayload(array $data, bool $isUpdate = false): array
    {
        $errors = [];
        $normalized = [];

        if (array_key_exists('title', $data)) {
            $title = trim((string) $data['title']);
            if ($title === '') {
                $errors['title'] = 'Title is required.';
            } elseif (mb_strlen($title) > 255) {
                $errors['title'] = 'Title must be 255 characters or fewer.';
            } else {
                $normalized['title'] = $title;
            }
        } elseif (!$isUpdate) {
            $errors['title'] = 'Title is required.';
        }

        if (array_key_exists('description', $data)) {
            $description = trim((string) $data['description']);
            if (mb_strlen($description) > 1000) {
                $errors['description'] = 'Description must be 1000 characters or fewer.';
            } else {
                $normalized['description'] = $description;
            }
        }

        if (array_key_exists('status', $data)) {
            $status = trim((string) $data['status']);
            if (!in_array($status, self::VALID_STATUSES, true)) {
                $errors['status'] = 'Status must be either Pending or Completed.';
            } else {
                $normalized['status'] = $status;
            }
        } elseif (!$isUpdate) {
            $normalized['status'] = 'Pending';
        }

        if (array_key_exists('priority', $data)) {
            $priority = trim((string) $data['priority']);
            if (!in_array($priority, self::VALID_PRIORITIES, true)) {
                $errors['priority'] = 'Priority must be Low, Medium, or High.';
            } else {
                $normalized['priority'] = $priority;
            }
        } elseif (!$isUpdate) {
            $normalized['priority'] = 'Medium';
        }

        return [
            'valid' => empty($errors),
            'data' => $normalized,
            'errors' => $errors,
        ];
    }

    public static function validateId($id): bool
    {
        return is_numeric($id) && (int) $id > 0 && (string) (int) $id === trim((string) $id);
    }
}
