<?php
//  example manager class
//
//  Follow convention in conf.ini file for invoking a custom
//  filter chain.
//
//  Keep in mind your core processor, normally SGL_Manager, can be whatever
//  you specify so, for example, in the case of implementing a REST server
//  you would not want to implement the usual validate, process and display
//  methods.  Create your own application controller implementation and subclass
//  that in your 'managers' or domain controllers.
class FooMgr
{

}