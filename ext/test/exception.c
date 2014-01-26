#include "header.h"


/**
 * Sample exception class
 */
ZEPHIR_INIT_CLASS(Test_Exception) {

	ZEPHIR_REGISTER_CLASS_EX(Test, Exception, test, exception, zend_exception_get_default(TSRMLS_C), NULL, 0);


	return SUCCESS;

}

