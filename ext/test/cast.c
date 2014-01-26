#include "header.h"


/**
 * X
 */
ZEPHIR_INIT_CLASS(Test_Cast) {

	ZEPHIR_REGISTER_CLASS(Test, Cast, test, cast, test_cast_method_entry, 0);


	return SUCCESS;

}

PHP_METHOD(Test_Cast, testCast1) {


	RETURN_LONG((int) 5.0);

}

PHP_METHOD(Test_Cast, testCast2) {


	RETURN_LONG(1);

}

