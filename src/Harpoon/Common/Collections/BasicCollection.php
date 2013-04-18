<?php
namespace Harpoon\Common\Collections;

use ErrorException;
use SplDoublyLinkedList;

class BasicCollection extends SplDoublyLinkedList {

	/**
	 * Кол-во удаленных элементов. Надо для offsetUnset в цикле.
	 *
	 * @var int
	 */
	private $deleted = 0;

	/**
	 * Удаляет элемент по индексу. Переделано для поддержки удаления в цикле.
	 * Если надо удалить элемент вне цикла, используйте rewind для сброса итератора
	 *
	 * @param int $index
	 */
	final public function offsetUnset($index) {
		$deleteIndex = $index - ($this->deleted++);
		if (parent::offsetExists($deleteIndex)) {
			parent::offsetUnset($deleteIndex);
		}
	}

	/**
	 * Сбрасывает итератор на начало списка. Нужно для offsetUnset
	 *
	 */
	final public function rewind() {
		parent::rewind();
		$this->deleted = 0;
	}

	/**
	 * Возвращает массив результатов toArray для содержащихся в себе объектов
	 *
	 * @return array
	 * @throws ErrorException
	 */
	public function toArray() {
		$result = array();

		foreach ($this as $item) {
			if (!method_exists($item, 'toArray')) {
				throw new ErrorException('У внутреннего объекта нет метода toArray');
			}
			$result[] = $item->toArray();
		}

		return $result;
	}
}
