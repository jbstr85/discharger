<?php
namespace Discharger
{

	class Discharger
	{

		/**
		 * Activates or disables object sharing
		 * @var sharing_enabled
		 */
		
		public $sharing_enabled;


		/**
		 * Container for interface bindings
		 * @var bindings
		 */

		private $bindings = [];

		/**
		 * Container for shareable objects
		 * @var dependencies
		 */
		
		private $dependencies = [];


		public function __construct($enable_sharing = true)
		{

			$this->sharing_enabled = $enable_sharing;

		}

		/**
		 * Returns ( or creates first ) an instance of shared object with constructor 
		 * @param  ReflectionClass $reflector, array $dependencies
		 * @return object
		 */
		
		private function handleShareableWithConstructor(\ReflectionClass $reflector, $dependencies)
		{
			if (!isset($this->dependencies[$reflector->getName()])) 
					
				$this->dependencies[$reflector->getName()] = $reflector->newInstanceArgs($dependencies);
		
				return $this->dependencies[$reflector->getName()];
		}

		/**
		 * Returns ( or creates first ) an instance of shared object without constructor
		 * @param  ReflectionClass $reflector
		 * @return object
		 */

		private function handleShareableWithoutConstructor(\ReflectionClass $reflector)
		{
			if (!isset($this->dependencies[$reflector->getName()]))
				
				$this->dependencies[$reflector->getName()] = $reflector->newInstanceWithoutConstructor();

				return $this->dependencies[$reflector->getName()];			
		}

		/**
		 * Creates an instance of non-shared object without constructor
		 * @param  ReflectionClass $reflector
		 * @return object
		 */

		private function handleReflectorWithoutConstructor(\ReflectionClass $reflector)
		{
			return $reflector->newInstanceWithoutConstructor();
		}

		/**
		 * Tests if reflector has a constructor
		 * @param  ReflectionClass $reflector
		 * @return bool
		 */


		private function hasConstructor(\ReflectionClass $reflector)
		{
			$constructor = $reflector->getConstructor();

			if (is_null($constructor))

				return false;

				return true;
		}

		/**
		 * Tests the paramter for Shareable interface 
		 * @param  ReflectionClass $reflector
		 * @return bool
		 */

		private function isShareable(\ReflectionClass $reflector)
		{		
			if($reflector->implementsInterface("Discharger\Shareable") && true === $this->sharing_enabled)

				return true;

				return false;
		}

		/**
		 * Decides what to do with a parameter without constructor
		 * @param  ReflectionClass $reflector
		 */


		private function handleNonConstructor(\ReflectionClass $reflector)
		{
			if ($this->isShareable($reflector))

				return $this->handleShareableWithoutConstructor($reflector);
				
				return $this->handleReflectorWithoutConstructor($reflector);
		}

		/**
		 * Returns an object with dependencies
		 * @param  string $module
		 * @return object
		 */


		public function fire($module)
		{
			$reflector = new \ReflectionClass($module);

			if (!$reflector->isInstantiable()) 

				return $this->checkIfIsBound($module);
			
			if (!$this->hasConstructor($reflector)) 

				return $this->handleNonConstructor($reflector);		

			$constructor = $reflector->getConstructor();

			$parameters = $constructor->getParameters();

			$dependencies = [];

			foreach ($parameters as $param) {
				
				$dependencies[] = $this->findDependencies($param);
			
			}

			if ($this->isShareable($reflector)) 

				return $this->handleShareableWithConstructor($reflector,$dependencies);	
		
				return $reflector->newInstanceArgs($dependencies);

		}

			

		/**
		 * Finds class dependencies recursively
		 * @param  ReflectionParameter $dependency
		 * 
		 */


		private function findDependencies(\ReflectionParameter $dependency)
		{
			$value = $dependency->getClass();

			if (!is_null($value)) 

				return $this->fire($value->name);

				return $this->resolveUnknownDependency($dependency);
		}

		/**
		 * Tries to resolve a non-class parameter
		 * @param  ReflectionParameter $dependency
		 * @throws Exception
		 * @return mixed
		 */


		private function resolveUnknownDependency(\ReflectionParameter $dependency)
		{
			if($dependency->isDefaultValueAvailable()) 

				return $dependency->getDefaultValue();

				throw new \Exception("Can't resolve an unknown parameter");
		}

		/**
		 * Binds an interface to a concrete implementation
		 * @param  string $abstract
		 * @param  string $concrete
		 *  
		 */

		public function bind(string $abstract, string $concrete = null)
		{
			if (is_string($concrete) && class_exists($concrete) && interface_exists($abstract)) 
				
				$this->bindings[$abstract] = $concrete;  
		}

		/**
		 * Tests if an interface is bound to a concrete class
		 * @param string $module
		 * @return string $module
		 * @throws Exception
		 */


		private function checkIfIsBound(string $module)
		{
			if (array_key_exists($module,$this->bindings)) 
				
				return $this->fire($this->bindings[$module]);
				
				throw new \Exception ("Class is not instantiable and is not bound to anything");
		}

		public function __get($prop)
		{
			return $this->$prop;
		}
	

	}
}