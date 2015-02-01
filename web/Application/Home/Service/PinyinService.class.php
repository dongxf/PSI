<?php
namespace Home\Service;

use \Overtrue\Pinyin\Pinyin;

require __DIR__ . '/../Common/Pinyin/Pinyin.php';

/**
 * 拼音Service
 *
 * @author 李静波
 */
class PinyinService {
	public function toPY($s) {
		Pinyin::set('delimiter', '');
		Pinyin::set('uppercase', true);
		Pinyin::set('only_chinese', false);
		return Pinyin::letter($s);
	}
}
