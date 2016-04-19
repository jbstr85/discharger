<?php

use Discharger\Discharger;

use Discharger\Shareable;

	class TestClass
	{

		public $dependency;

		public function __construct(TestClassDependency $dependency)
		{
			$this->dependency = $dependency;

		}

		public function getClass()
		{
			return __CLASS__;

		}

	}


	class TestClassDependency
	{
		public function foo()
		{

			return "I am foo";

		}

	}


	class InstanceOfTest extends PHPUnit_Framework_TestCase
	{

		

		public function setUp()
		{
			$this->discharger = new Discharger();

		}


		public function testFailure()
		{
			$this->assertInstanceOf('TestClass', $this->discharger->fire('TestClass'));

		}


	}