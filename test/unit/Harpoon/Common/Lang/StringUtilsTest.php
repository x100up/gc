<?php
namespace test\unit\Harpoon\Common\Lang;

use Harpoon\Common\Lang\StringUtils;

class StringUtilsTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider providerRemoveColorWords
	 * @param $str
	 * @param $expected
	 */
	public function testRemoveColorWords($str, $expected) {
		$result = StringUtils::removeColorWords($str);
		$this->assertEquals($expected, $result);
	}

	public static function providerRemoveColorWords() {
		return array(
			array('1 2 3 black ', '1 2 3'),
			array('1 2 3 black 4', '1 2 3 4'),
			array('white', ''),
			array('white 3', '3'),
			array('white 3 black', '3'),
		);
	}

	/**
	 * @dataProvider providerIsContainsWord
	 * @param $str
	 * @param $word
	 * @param $expected
	 */
	public function testIsContainsWord($str, $word, $expected) {
		$isContains = StringUtils::isContainsWord($str, $word);
		$this->assertEquals($expected, $isContains);
	}

	public static function providerIsContainsWord() {
		return array(
			array('a b c', 'a', true),
			array('a b c', 'b', true),
			array('a b c', 'c', true),
			array('a b c', 'd', false),
			array('a b c', 'b c', false),
			array('a b c', '', false),
			array('', '', false),
			array('', 'd', false),
		);
	}

	/**
	 * @dataProvider providerIsOnlyNumeric
	 * @param $str
	 * @param $expected
	 */
	public function testIsOnlyNumeric($str, $expected) {
		$result = StringUtils::isOnlyNumeric($str);
		$this->assertEquals($expected, $result);
	}

	public static function providerIsOnlyNumeric() {
		return array(
			array('123', true),
			array('1', true),
			array('1 2 3', false),
			array('1 a 3', false),
			array('a', false),
			array('b a c', false),
		);
	}

	/**
	 * @dataProvider providerIsOnlyLetters
	 * @param $str
	 * @param $expected
	 */
	public function testIsOnlyLetters($str, $expected) {
		$result = StringUtils::isOnlyLetters($str);
		$this->assertEquals($expected, $result);
	}

	public static function providerIsOnlyLetters() {
		return array(
			array('a', true),
			array('bac', true),
			array('bAc', true),
			array('АБВ', true),
			array('АбВ', true),
			array('аб', true),
			array('aspire', true),
			array('Aspire', true),
			array('123', false),
			array('1', false),
			array('1 2 3', false),
			array('1 a 3', false),
			array('1a3', false),
			array('1aБ', false),
		);
	}

	/**
	 * @test
	 * @dataProvider providerReplaceBadText
	 *
	 * @param $str
	 * @param $expected
	 */
	public function replaceBadText($str, $expected) {
		$result = StringUtils::replaceBadText($str);
		self::assertEquals($expected, $result);
	}

	public static function providerReplaceBadText() {
		return array(
			array('', ''),
			array('1', '1'),
			array('0', '0'),
			array('йцу456           ', 'йцу456'),
			array('qqqqq', 'qqqqq'),
			array('q qq qq', 'q qq qq'),
			array('q qq    qq', 'q qq qq'),
			array(' q q    ', 'q q'),
			array(' q-q  -  ', 'q-q -'),
			array(' q-q  ++  ', 'q-q ++'),
			array(' ,qq', ',qq'),
			array(' .а  ус.', '.а ус.'),
			array(' йй""йй', 'йй""йй'),
			array('  йййй!! ', 'йййй!!'),
			array('  й?? ййй!! ', 'й?? ййй!!'),
			array('qqqq\\q', 'qqqq\\q'),
			array('e(e', 'e(e'),
			array('e)e', 'e)e'),
			array('цц]цц', 'цц]цц'),
			array('цц[  цц', 'цц[ цц'),
			array('цц%цц', 'цц%цц'),
			array('цц$$ц$ц', 'цц$$ц$ц'),
			array('ц % !   % ц', 'ц % ! % ц'),
			array('qqqq;q', 'qqqq;q'),
			array('qqqq::q', 'qqqq::q'),
			array('q/q/qqq', 'q/q/qqq'),
			array('q&qqqq', 'q&qqqq'),
			array('q\qqqq', 'q\qqqq'),
			array("!'!", "!'!"),
			array('q*!! !qqqq', 'q*!! !qqqq'),
			array('q_ ____qqqq', 'q_ ____qqqq'),
			array('QQ=  ', 'QQ='),
			array('  Q###Q  ', 'Q###Q'),
			array('цу!!кцу№кцук№', 'цу!!кцу№кцук№'),
			array('a-zаøø-яё0-9-+ ,."!?\()[]%$>;:/&', 'a-zа -яё0-9-+ ,."!?\()[]%$ ;:/&'),
			array('(₳ ฿ ₵ ¢ ₡ ₢ ₠ $', '( $'),
			array('₳      ', ''),
		);
	}

	/**
	 * @test
	 */
	public function generateRandom() {

		self::assertEquals('', StringUtils::generateRandom(0), 'На выходе д.б. пустая строка');
		self::assertEquals(10, strlen(StringUtils::generateRandom(10)));
		self::assertEquals(10, strlen(StringUtils::generateRandom(10, true)));
		self::assertEquals(10, strlen(StringUtils::generateRandom(10, null, true)));
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function generateRandomInvalidLength() {
		StringUtils::generateRandom('hello');
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function generateRandomExcludedCharsAndDigits() {
		StringUtils::generateRandom(10, true, true);
	}


	/**
	 * @dataProvider providerRemoveBom
	 * @test
	 */
	public function removeBOM($file) {
		$file = __DIR__.'/StringUtilsTest/'.$file;
		$content = file_get_contents($file);
		$content = StringUtils::removeBOM($content);
		self::assertEquals('я', $content);
	}

	public static function providerRemoveBom() {
		return array(
			array('bom.csv'),
			array('without_bom.csv'),
		);

	}
}
