<?php

namespace Application\Service;

use Application\Entity\SystemSetting;
use Doctrine\ORM\EntityManager;
use DomainException;
use InvalidArgumentException;
use RuntimeException;

class SettingsService
{
    private const ALLOWED_SETTINGS = [
        'borrow_fee',
        'penalty_rate',
        'borrowing_period',
        'borrowing_limit',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function getSettings(): array
    {
        $settings = [];

        foreach (self::ALLOWED_SETTINGS as $key) {
            $setting = $this->entityManager
                ->getRepository(SystemSetting::class)
                ->find($key);

            if ($setting === null) {
                throw new RuntimeException(
                    'Missing system setting: ' . $key
                );
            }

            $settings[$key] = $setting->getValue();
        }

        return $settings;
    }

    public function updateSettings(array $values): array
    {
        if ($values === []) {
            throw new InvalidArgumentException(
                'No settings provided'
            );
        }

        foreach ($values as $key => $value) {
            if (!in_array($key, self::ALLOWED_SETTINGS, true)) {
                throw new InvalidArgumentException(
                    'Unknown setting: ' . $key
                );
            }

            if (!is_string($value) && !is_int($value)) {
                throw new InvalidArgumentException(
                    'Invalid value for: ' . $key
                );
            }

            $value = (string) $value;

            if (in_array(
                $key,
                ['borrowing_period', 'borrowing_limit'],
                true
            )) {
                if (!preg_match('/^[1-9][0-9]*$/', $value)) {
                    throw new InvalidArgumentException(
                        'Invalid value for: ' . $key
                    );
                }
            } else {
                if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $value)) {
                    throw new InvalidArgumentException(
                        'Invalid value for: ' . $key
                    );
                }
            }
        }

        $settings = $this->entityManager
            ->wrapInTransaction(function (EntityManager $em) use ($values) {
                $repository = $em->getRepository(SystemSetting::class);

                foreach ($values as $key => $value) {
                    $setting = $repository->find($key);

                    if ($setting === null) {
                        throw new RuntimeException(
                            'Missing system setting: ' . $key
                        );
                    }

                    $setting->setValue((string) $value);
                }

                return true;
            });

        return $this->getSettings();
    }
}
