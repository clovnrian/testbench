<?php

namespace Testbench;

use Nette\ComponentModel\IComponent;

require_once __DIR__ . '/../Helpers.php';

trait TComponent
{

	private $__testbench_presenterMock;

	protected function attachToPresenter(IComponent $component, $name = NULL)
	{
		if ($name === NULL) {
			if (!$name = $component->getName()) {
				$name = $component->getReflection()->getShortName();
				if (preg_match('~class@anonymous.*~', $name)) {
					$name = md5($name);
				}
			}
		}
		if (!$this->__testbench_presenterMock) {
			$this->__testbench_presenterMock = __testbench_getService('Testbench\PresenterMock');
			$this->__testbench_presenterMock->invalidLinkMode = \Nette\Application\UI\Presenter::INVALID_LINK_EXCEPTION;
			$container = \Testbench\ContainerFactory::create(FALSE);
			$container->callInjects($this->__testbench_presenterMock);
		}
		$this->__testbench_presenterMock->onStartup[] = function (PresenterMock $presenter) use ($component, $name) {
			try {
				$presenter->removeComponent($component);
			} catch (\Nette\InvalidArgumentException $exc) {
			}
			$presenter->addComponent($component, $name);
		};
		$this->__testbench_presenterMock->run(new ApplicationRequestMock);
	}

	protected function checkRenderOutput(IComponent $control, $expected)
	{
		if (!$control->getParent()) {
			$this->attachToPresenter($control);
		}
		ob_start();
		$control->render();
		if (is_file($expected)) {
			\Tester\Assert::matchFile($expected, ob_get_clean());
		} else {
			\Tester\Assert::match($expected, ob_get_clean());
		}
	}

}
