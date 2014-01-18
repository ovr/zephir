
#ifdef HAVE_CONFIG_H
#include "../ext_config.h"
#endif

#include <php.h>
#include "../php_ext.h"
#include "../ext.h"

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/main.h"
#include "kernel/memory.h"
#include "kernel/array.h"
#include "kernel/variables.h"
#include "ext/spl/spl_exceptions.h"
#include "kernel/exception.h"


ZEPHIR_INIT_CLASS(Test_Vars) {

	ZEPHIR_REGISTER_CLASS(Test, Vars, test, vars, test_vars_method_entry, 0);


	return SUCCESS;

}

PHP_METHOD(Test_Vars, testVarDump) {

	zval *a, *ar, *_0;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(a);
	ZVAL_STRING(a, "hello", 1);
	ZEPHIR_INIT_VAR(ar);
	array_init_size(ar, 4);
	ZEPHIR_INIT_VAR(_0);
	ZVAL_LONG(_0, 1);
	zephir_array_fast_append(ar, _0);
	ZEPHIR_INIT_BNVAR(_0);
	ZVAL_STRING(_0, "world", 1);
	zephir_array_fast_append(ar, _0);
	zephir_array_fast_append(ar, ZEPHIR_GLOBAL(global_false));
	zephir_var_dump(&(ar) TSRMLS_CC);
	zephir_var_dump(&(a) TSRMLS_CC);
	ZEPHIR_MM_RESTORE();

}

PHP_METHOD(Test_Vars, testVarExport) {

	zval *a, *ar, *ret, *_0;

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(a);
	ZVAL_STRING(a, "hello", 1);
	ZEPHIR_INIT_VAR(ar);
	array_init_size(ar, 4);
	ZEPHIR_INIT_VAR(_0);
	ZVAL_LONG(_0, 1);
	zephir_array_fast_append(ar, _0);
	ZEPHIR_INIT_BNVAR(_0);
	ZVAL_STRING(_0, "world", 1);
	zephir_array_fast_append(ar, _0);
	zephir_array_fast_append(ar, ZEPHIR_GLOBAL(global_false));
	zephir_var_export(&(ar) TSRMLS_CC);
	ZEPHIR_INIT_VAR(ret);
	zephir_var_export_ex(ret, &(ar) TSRMLS_CC);
	zephir_var_export(&(a) TSRMLS_CC);
	ZEPHIR_INIT_BNVAR(ret);
	zephir_var_export_ex(ret, &(a) TSRMLS_CC);
	RETURN_CCTOR(ret);

}

PHP_METHOD(Test_Vars, test88Issue) {

	zval *param1_param = NULL, *param2_param = NULL;
	zval *param1 = NULL, *param2 = NULL;

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 1, &param1_param, &param2_param);

	if (Z_TYPE_P(param1_param) != IS_STRING && Z_TYPE_P(param1_param) != IS_NULL) {
		zephir_throw_exception_string(spl_ce_InvalidArgumentException, SL("Parameter 'param1' must be a string") TSRMLS_CC);
		RETURN_MM_NULL();
	}

	if (Z_TYPE_P(param1_param) == IS_STRING) {
		param1 = param1_param;
	} else {
		ZEPHIR_INIT_VAR(param1);
		ZVAL_EMPTY_STRING(param1);
	}
	if (param2_param == NULL) {
		ZEPHIR_INIT_VAR(param2);
		ZVAL_EMPTY_STRING(param2);
	}


	zephir_var_dump(&(param1) TSRMLS_CC);
	zephir_var_dump(&(param2) TSRMLS_CC);
	zephir_var_export(&(param1) TSRMLS_CC);
	zephir_var_export(&(param2) TSRMLS_CC);
	ZEPHIR_MM_RESTORE();

}

PHP_METHOD(Test_Vars, test88IssueParam2InitString) {

	zval *param1_param = NULL, *param2_param = NULL;
	zval *param1 = NULL, *param2 = NULL;

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 1, &param1_param, &param2_param);

	if (Z_TYPE_P(param1_param) != IS_STRING && Z_TYPE_P(param1_param) != IS_NULL) {
		zephir_throw_exception_string(spl_ce_InvalidArgumentException, SL("Parameter 'param1' must be a string") TSRMLS_CC);
		RETURN_MM_NULL();
	}

	if (Z_TYPE_P(param1_param) == IS_STRING) {
		param1 = param1_param;
	} else {
		ZEPHIR_INIT_VAR(param1);
		ZVAL_EMPTY_STRING(param1);
	}
	if (param2_param == NULL) {
		ZEPHIR_INIT_VAR(param2);
		ZVAL_STRING(param2, "test string", 1);
	}


	zephir_var_export(&(param2) TSRMLS_CC);
	ZEPHIR_MM_RESTORE();

}

