/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './sass/app.sass';

require("./js/bootstrap_vanilla");
require("./js/font_awesome_light");
require("./js/font_awesome_regular");
require("./js/font_awesome_solid");

// apps
require('./js/apps/Assign/bootstrap');
require('./js/apps/Confirm/bootstrap');
require('./js/apps/Trade/bootstrap');
require('./js/apps/Generate/bootstrap');
