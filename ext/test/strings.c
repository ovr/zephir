#include "header.h"
#include "kernel/string.h"
#include "kernel/memory.h"
#include "kernel/operators.h"


ZEPHIR_INIT_CLASS(Test_Strings) {

	ZEPHIR_REGISTER_CLASS(Test, Strings, test, strings, test_strings_method_entry, 0);


	return SUCCESS;

}

PHP_METHOD(Test_Strings, testTrim) {

	zval *str;

	zephir_fetch_params(0, 1, 0, &str);



	zephir_fast_trim(return_value, str, NULL , ZEPHIR_TRIM_BOTH TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testRtrim) {

	zval *str;

	zephir_fetch_params(0, 1, 0, &str);



	zephir_fast_trim(return_value, str, NULL , ZEPHIR_TRIM_RIGHT TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testLtrim) {

	zval *str;

	zephir_fetch_params(0, 1, 0, &str);



	zephir_fast_trim(return_value, str, NULL , ZEPHIR_TRIM_LEFT TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testTrim2Params) {

	zval *str, *charlist;

	zephir_fetch_params(0, 2, 0, &str, &charlist);



	zephir_fast_trim(return_value, str, charlist, ZEPHIR_TRIM_BOTH TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testRtrim2Params) {

	zval *str, *charlist;

	zephir_fetch_params(0, 2, 0, &str, &charlist);



	zephir_fast_trim(return_value, str, charlist, ZEPHIR_TRIM_RIGHT TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testLtrim2Params) {

	zval *str, *charlist;

	zephir_fetch_params(0, 2, 0, &str, &charlist);



	zephir_fast_trim(return_value, str, charlist, ZEPHIR_TRIM_LEFT TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testImplode) {

	zval *glue, *pieces;

	zephir_fetch_params(0, 2, 0, &glue, &pieces);



	zephir_fast_join(return_value, glue, pieces TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testStrpos) {

	zval *haystack, *needle;

	zephir_fetch_params(0, 2, 0, &haystack, &needle);



	zephir_fast_strpos(return_value, haystack, needle, 0 );
	return;

}

PHP_METHOD(Test_Strings, testStrposOffset) {

	int offset;
	zval *haystack, *needle, *offset_param = NULL, _0;

	zephir_fetch_params(0, 3, 0, &haystack, &needle, &offset_param);

		offset = zephir_get_intval(offset_param);


	ZEPHIR_SINIT_VAR(_0);
	ZVAL_LONG(&_0, offset);
	zephir_fast_strpos(return_value, haystack, needle, zephir_get_intval(&_0) );
	return;

}

PHP_METHOD(Test_Strings, testExplode) {

	zval *delimiter, *str;

	zephir_fetch_params(0, 2, 0, &delimiter, &str);



	zephir_fast_explode(return_value, delimiter, str, LONG_MAX TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testExplodeStr) {

	zval *str;

	zephir_fetch_params(0, 1, 0, &str);



	zephir_fast_explode_str(return_value, SL(","), str, LONG_MAX TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testExplodeLimit) {

	int limit;
	zval *str, *limit_param = NULL, _0;

	zephir_fetch_params(0, 2, 0, &str, &limit_param);

		limit = zephir_get_intval(limit_param);


	ZEPHIR_SINIT_VAR(_0);
	ZVAL_LONG(&_0, limit);
	zephir_fast_explode_str(return_value, SL(","), str, zephir_get_intval(&_0)  TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testSubstr) {

	int from, len;
	zval *str, *from_param = NULL, *len_param = NULL, _0, _1;

	zephir_fetch_params(0, 3, 0, &str, &from_param, &len_param);

		from = zephir_get_intval(from_param);
		len = zephir_get_intval(len_param);


	ZEPHIR_SINIT_VAR(_0);
	ZVAL_LONG(&_0, from);
	ZEPHIR_SINIT_VAR(_1);
	ZVAL_LONG(&_1, len);
	zephir_substr(return_value, str, zephir_get_intval(&_0) , zephir_get_intval(&_1) );
	return;

}

PHP_METHOD(Test_Strings, testSubstr2) {

	int from;
	zval *str, *from_param = NULL, _0;

	zephir_fetch_params(0, 2, 0, &str, &from_param);

		from = zephir_get_intval(from_param);


	ZEPHIR_SINIT_VAR(_0);
	ZVAL_LONG(&_0, from);
	zephir_substr(return_value, str, zephir_get_intval(&_0) , 0 );
	return;

}

PHP_METHOD(Test_Strings, testSubstr3) {

	zval *str;

	zephir_fetch_params(0, 1, 0, &str);



	zephir_substr(return_value, str, -1 , 0 );
	return;

}

PHP_METHOD(Test_Strings, testSubstr4) {

	zval *str;

	zephir_fetch_params(0, 1, 0, &str);



	zephir_substr(return_value, str, 0 , -1 );
	return;

}

PHP_METHOD(Test_Strings, testAddslashes) {

	zval *str;

	zephir_fetch_params(0, 1, 0, &str);



	zephir_addslashes(return_value, str TSRMLS_CC);
	return;

}

PHP_METHOD(Test_Strings, testStripslashes) {

	zval *str;

	zephir_fetch_params(0, 1, 0, &str);



	zephir_stripslashes(return_value, str TSRMLS_CC);
	return;

}

