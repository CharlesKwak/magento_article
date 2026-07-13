<?php

/**
 * Minimal Magento Framework stubs for repository smoke tests.
 * Not a substitute for a real Magento install — only enough to autoload
 * registration.php and reflect/mock module classes under PHPUnit.
 */

namespace Magento\Framework\Component {
    if (!class_exists(ComponentRegistrar::class, false)) {
        class ComponentRegistrar
        {
            public const MODULE = 'module';
            public const LIBRARY = 'library';
            public const THEME = 'theme';
            public const LANGUAGE = 'language';

            /** @var array<string, array<string, string>> */
            private static $paths = [];

            public static function register($type, $componentName, $path): void
            {
                self::$paths[$type][$componentName] = $path;
            }

            public static function getPath($type, $componentName): ?string
            {
                return self::$paths[$type][$componentName] ?? null;
            }
        }
    }
}

namespace Magento\Framework {
    if (!interface_exists(ObjectManagerInterface::class, false)) {
        interface ObjectManagerInterface
        {
            /**
             * @param string $type
             * @param array $arguments
             * @return mixed
             */
            public function create($type, array $arguments = []);

            /**
             * @param string $type
             * @return mixed
             */
            public function get($type);

            /**
             * @param mixed $object
             * @return void
             */
            public function configure(array $configuration);
        }
    }
}

namespace Magento\Framework\Model\ResourceModel\Db\Collection {
    if (!class_exists(AbstractCollection::class, false)) {
        /**
         * Lightweight stand-in so Collection classes can be loaded for reflection.
         * Not used for real DB operations in smoke tests.
         */
        abstract class AbstractCollection implements \IteratorAggregate, \Countable
        {
            /** @var array<int, mixed> */
            protected $_items = [];

            protected function _construct(): void
            {
            }

            /**
             * @param string $model
             * @param string $resourceModel
             */
            protected function _init($model, $resourceModel): void
            {
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator($this->_items);
            }

            public function count(): int
            {
                return count($this->_items);
            }
        }
    }
}
