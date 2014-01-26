#include "header.h"
#include "kernel/memory.h"
#include "kernel/object.h"


/**
 * OO operations
 */
ZEPHIR_INIT_CLASS(Test_Typeoff) {

	ZEPHIR_REGISTER_CLASS(Test, Typeoff, test, typeoff, test_typeoff_method_entry, 0);

	zend_declare_property_null(test_typeoff_ce, SL("property"), ZEND_ACC_PROTECTED TSRMLS_CC);

	return SUCCESS;

}

PHP_METHOD(Test_Typeoff, testNativeStringFalse) {

	zval *testVar;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(testVar);
	ZVAL_STRING(testVar, "sdfsdf", 1);
	RETURN_MM_BOOL(0);

}

PHP_METHOD(Test_Typeoff, testNativeStringTrue) {

	zval *testVar;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(testVar);
	ZVAL_STRING(testVar, "sdfsdf", 1);
	RETURN_MM_BOOL(1);

}

PHP_METHOD(Test_Typeoff, testNativeIntFalse) {

	int testVar;


	testVar = 12345;
	RETURN_BOOL(0);

}

PHP_METHOD(Test_Typeoff, testNativeIntTrue) {

	int testVar;


	testVar = 12345;
	RETURN_BOOL(1);

}

PHP_METHOD(Test_Typeoff, testNativeDoubleTrue) {

	double testVar;


	testVar = (double) (12345);
	RETURN_BOOL(1);

}

PHP_METHOD(Test_Typeoff, testNativeBoolTrue) {

	zend_bool testVar;


	testVar = 1;
	RETURN_BOOL(1);

}

PHP_METHOD(Test_Typeoff, testNativeBoolFalse) {

	zend_bool testVar;


	testVar = 0;
	RETURN_BOOL(0);

}

PHP_METHOD(Test_Typeoff, testArrayFalse) {

	zval *testVar;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(testVar);
	array_init(testVar);
	RETURN_MM_BOOL((Z_TYPE_P(testVar) == IS_STRING));

}

PHP_METHOD(Test_Typeoff, testArrayTrue) {

	zval *testVar;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(testVar);
	array_init(testVar);
	RETURN_MM_BOOL((Z_TYPE_P(testVar) == IS_ARRAY));

}

PHP_METHOD(Test_Typeoff, testClassPropertyAccess) {

	zval *_0, *_1;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_ZVAL_NREF(_0);
	ZVAL_STRING(_0, "test string", 1);
	zephir_update_property_this(this_ptr, SL("property"), _0 TSRMLS_CC);
	ZEPHIR_OBS_VAR(_1);
	zephir_read_property_this(&_1, this_ptr, SL("property"), PH_NOISY_CC);
	RETURN_MM_BOOL((Z_TYPE_P(_1) == IS_STRING));

}

