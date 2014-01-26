#include "header.h"


/**
 * Sample exception class
 */
ZEPHIR_INIT_CLASS(Test_TestInterface) {

	ZEPHIR_REGISTER_INTERFACE(Test, TestInterface, test, testinterface, NULL);

	zend_declare_class_constant_null(test_testinterface_ce, SL("C1") TSRMLS_CC);
	zend_declare_class_constant_bool(test_testinterface_ce, SL("C2"), 0 TSRMLS_CC);
	zend_declare_class_constant_bool(test_testinterface_ce, SL("C3"), 1 TSRMLS_CC);
	zend_declare_class_constant_long(test_testinterface_ce, SL("C4"), 10 TSRMLS_CC);
	zend_declare_class_constant_double(test_testinterface_ce, SL("C5"), 10.25 TSRMLS_CC);
	zend_declare_class_constant_string(test_testinterface_ce, SL("C6"), "test" TSRMLS_CC);

	return SUCCESS;

}

