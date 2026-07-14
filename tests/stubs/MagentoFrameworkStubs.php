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

namespace Magento\Framework\Model {
    if (!class_exists(AbstractModel::class, false)) {
        /**
         * Lightweight stand-in so entity models (Post, Category, …) can be
         * instantiated with plain array data in smoke tests.
         */
        abstract class AbstractModel
        {
            /** @var array<string, mixed> */
            protected $_data = [];

            public function __construct(array $data = [])
            {
                $this->_data = $data;
            }

            public function getId()
            {
                return $this->_data['id'] ?? null;
            }

            public function setId($id)
            {
                $this->_data['id'] = $id;
                return $this;
            }

            public function getData($key = '')
            {
                if ($key === '') {
                    return $this->_data;
                }
                return $this->_data[$key] ?? null;
            }

            public function setData($key, $value = null)
            {
                if (is_array($key)) {
                    $this->_data = $key;
                } else {
                    $this->_data[$key] = $value;
                }
                return $this;
            }

            /**
             * Magic getX()/setX() mapped to snake_case data keys.
             */
            public function __call($method, $args)
            {
                $key = strtolower((string) preg_replace('/(.)([A-Z])/', '$1_$2', substr($method, 3)));
                if (strpos($method, 'get') === 0) {
                    return $this->_data[$key] ?? null;
                }
                if (strpos($method, 'set') === 0) {
                    $this->_data[$key] = $args[0] ?? null;
                    return $this;
                }
                throw new \BadMethodCallException($method);
            }

            /**
             * @param string $resourceModel
             */
            protected function _init($resourceModel): void
            {
            }
        }
    }
}

namespace Magento\Framework\DataObject {
    if (!interface_exists(IdentityInterface::class, false)) {
        interface IdentityInterface
        {
            /**
             * @return string[]
             */
            public function getIdentities();
        }
    }
}

namespace Magento\Store\Model {
    if (!interface_exists(StoreManagerInterface::class, false)) {
        interface StoreManagerInterface
        {
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
