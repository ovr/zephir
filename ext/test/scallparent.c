#include "header.h"


/**
 * Static Function calls
 */
ZEPHIR_INIT_CLASS(Test_ScallParent) {

	ZEPHIR_REGISTER_CLASS(Test, ScallParent, test, scallparent, test_scallparent_method_entry, 0);


	return SUCCESS;

}

PHP_METHOD(Test_ScallParent, testMethod1) {


	RETURN_STRING("hello parent public", 1);

}

PHP_METHOD(Test_ScallParent, testMethod2) {


	RETURN_STRING("hello parent protected", 1);

}

