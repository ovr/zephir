#include "header.h"
#include "kernel/memory.h"


ZEPHIR_INIT_CLASS(Test_Constants) {

	ZEPHIR_REGISTER_CLASS_EX(Test, Constants, test, constants, test_constantsparent_ce, test_constants_method_entry, 0);

	zend_declare_class_constant_null(test_constants_ce, SL("C1") TSRMLS_CC);
	zend_declare_class_constant_bool(test_constants_ce, SL("C2"), 0 TSRMLS_CC);
	zend_declare_class_constant_bool(test_constants_ce, SL("C3"), 1 TSRMLS_CC);
	zend_declare_class_constant_long(test_constants_ce, SL("C4"), 10 TSRMLS_CC);
	zend_declare_class_constant_double(test_constants_ce, SL("C5"), 10.25 TSRMLS_CC);
	zend_declare_class_constant_string(test_constants_ce, SL("C6"), "test" TSRMLS_CC);

	return SUCCESS;

}

PHP_METHOD(Test_Constants, testReadConstant) {


	RETURN_LONG(3);

}

PHP_METHOD(Test_Constants, testReadClassConstant1) {


	RETURN_LONG(10);

}

PHP_METHOD(Test_Constants, testReadClassConstant2) {


	RETURN_LONG(10);

}

PHP_METHOD(Test_Constants, testReadClassConstant3) {


	RETURN_LONG(10);

}

PHP_METHOD(Test_Constants, testPHPVersionEnvConstant) {


	ZEPHIR_GET_CONSTANT(return_value, "PHP_VERSION");
	return;

}

PHP_METHOD(Test_Constants, testClassMagicConstant) {


	RETURN_STRING("Constants", 1);

}

PHP_METHOD(Test_Constants, testMethodMagicConstant) {


	RETURN_STRING("Constants:testMethodMagicConstant", 1);

}

PHP_METHOD(Test_Constants, testFunctionMagicConstant) {


	RETURN_STRING("testFunctionMagicConstant", 1);

}

PHP_METHOD(Test_Constants, testNamespaceMagicConstant) {


	RETURN_STRING("Test", 1);

}

PHP_METHOD(Test_Constants, testDirConstant) {


	RETURN_NULL();

}

PHP_METHOD(Test_Constants, testPHPVersionEnvConstantInExpValue) {

	zval *a;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(a);
	ZEPHIR_GET_CONSTANT(a, "PHP_VERSION");
	RETURN_CCTOR(a);

}

