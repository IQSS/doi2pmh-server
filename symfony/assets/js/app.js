import '../css/app.scss';

/**
 * Includes project's JS files.
 */
require('bootstrap');
require('./citation');
require('./user');
require('./doi');
require('./configuration')
/**
 * Init main object
 * @type {*|{}}
 */
window.doi2pmh = window.doi2pmh || {};
