#include "header.h"
#include "kernel/fcall.h"
#include "kernel/memory.h"


/**
 * Call external static functions
 */
ZEPHIR_INIT_CLASS(Test_ScallExternal) {

	ZEPHIR_REGISTER_CLASS(Test, ScallExternal, test, scallexternal, test_scallexternal_method_entry, 0);


	return SUCCESS;

}

PHP_METHOD(Test_ScallExternal, testCall1) {

	ZEPHIR_MM_GROW();

	zephir_call_static(return_value, "Test\\Scall", "testmethod1");
	RETURN_MM();

}

PHP_METHOD(Test_ScallExternal, testCall2) {

	zval *a, *b;

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &a, &b);



	zephir_call_static_p2(return_value, "Test\\Scall", "testmethod4", a, b);
	RETURN_MM();

}

