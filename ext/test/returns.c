#include "header.h"


/**
 * X
 */
ZEPHIR_INIT_CLASS(Test_Returns) {

	ZEPHIR_REGISTER_CLASS(Test, Returns, test, returns, test_returns_method_entry, 0);


	return SUCCESS;

}

PHP_METHOD(Test_Returns, testReturnCast1) {


	RETURN_LONG((int) 5.0);

}

PHP_METHOD(Test_Returns, testReturnCast2) {


	RETURN_LONG(1);

}

PHP_METHOD(Test_Returns, testReturnCast3) {


	RETURN_LONG(0);

}

