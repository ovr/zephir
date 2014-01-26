#include "header.h"
#include "kernel/memory.h"
#include "kernel/string.h"
#include "kernel/fcall.h"
#include "ext/spl/spl_exceptions.h"
#include "kernel/exception.h"


/**
 * Function calls
 */
ZEPHIR_INIT_CLASS(Test_Fcall) {

	ZEPHIR_REGISTER_CLASS(Test, Fcall, test, fcall, test_fcall_method_entry, 0);


	return SUCCESS;

}

PHP_METHOD(Test_Fcall, testCall1) {

	zval _0, _1;


	ZEPHIR_SINIT_VAR(_0);
	ZVAL_STRING(&_0, "hello", 0);
	ZEPHIR_SINIT_VAR(_1);
	ZVAL_STRING(&_1, "h", 0);
	zephir_fast_strpos(return_value, &_0, &_1, 0 );
	return;

}

PHP_METHOD(Test_Fcall, testCall1FromVar) {

	zval *funcName, *_0, *_1;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(funcName);
	ZVAL_STRING(funcName, "strpos", 1);
	ZEPHIR_INIT_VAR(_0);
	ZVAL_STRING(_0, "hello", 1);
	ZEPHIR_INIT_VAR(_1);
	ZVAL_STRING(_1, "l", 1);
	if (Z_TYPE_P(funcName) != IS_STRING) {
		zephir_throw_exception_string(spl_ce_InvalidArgumentException, SL("Variable funcName must be a string") TSRMLS_CC);
		RETURN_MM_NULL();
	}
	zephir_call_func_p2(return_value, Z_STRVAL_P(funcName), _0, _1);
	RETURN_MM();

}

PHP_METHOD(Test_Fcall, testStrtokFalse) {


	RETURN_BOOL(0);

}

PHP_METHOD(Test_Fcall, testStrtokVarBySlash) {

	zval *value, _0;

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &value);



	ZEPHIR_SINIT_VAR(_0);
	ZVAL_STRING(&_0, "/", 0);
	zephir_call_func_p2(return_value, "strtok", value, &_0);
	RETURN_MM();

}

