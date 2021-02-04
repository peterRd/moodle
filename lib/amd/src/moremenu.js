// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moves wrapping navigation items into a more menu.
 *
 * @module     core/moremenu
 * @class      moremenu
 * @package    core
 * @copyright  2021 Moodle
 * @author     Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {classUtil} from 'core/utils';

/**
 * Moremenu constants.
 */
const sel = {
    regions: {
        moredropdown: '[data-region="moredropdown"]',
    },
    classes: {
        nav: 'nav',
        navlink: 'nav-link',
        dropdownitem: 'dropdown-item',
        dropdownmoremenu: 'dropdownmoremenu',
        observed: 'observed',
        hidden: 'd-none',
    }
};

/**
 * Auto Collapse navigation items that wrap into a dropdown menu.
 *
 * @param {Object} container The navbar container.
 */
const autoCollapse = (menu) => {

    let navHeight = menu.offsetHeight;
    let maxHeight = menu.parentNode.offsetHeight;

    if (window.outerWidth < 768) {
        navHeight = maxHeight;
    }

    const dropdownMenu = menu.querySelector(sel.regions.moredropdown);
    const dropdown = menu.querySelector('.' + sel.classes.dropdownmoremenu);

    if (navHeight > maxHeight) {

        dropdown.classList.remove(sel.classes.hidden);

        if ('children' in menu) {
            const menuNodes = Array.from(menu.children).reverse();
            menuNodes.forEach( (item) => {
                if (!item.classList.contains(sel.classes.dropdownmoremenu)) {
                    if (menu.offsetHeight > maxHeight) {
                        const lastNode = menu.removeChild(item);
                        const navLink = lastNode.querySelector('.' + sel.classes.navlink);
                        navLink.setAttribute('role', 'menuitem');
                        classUtil('replace', navLink, sel.classes.navlink, sel.classes.dropdownitem);
                        dropdownMenu.prepend(lastNode);
                    }
                }
            });
        }
    }
    else {
        dropdown.classList.add(sel.classes.hidden);

        if ('children' in dropdownMenu) {
            const menuNodes = Array.from(dropdownMenu.children);
            menuNodes.forEach( (item) => {
                if (window.outerWidth < 992 || menu.offsetHeight < maxHeight) {
                    const lastNode = dropdownMenu.removeChild(item);
                    const navLink = lastNode.querySelector('.' + sel.classes.dropdownitem);
                    navLink.removeAttribute('role');
                    classUtil('replace', navLink, sel.classes.dropdownitem, sel.classes.navlink);
                    menu.insertBefore(lastNode, dropdown);
                }
            });
        }
        if (window.outerWidth > 768) {
            navHeight = menu.offsetHeight;
            if (navHeight > maxHeight) {
                autoCollapse(menu);
            }
        }
    }
    classUtil('add', menu.parentNode, sel.classes.observed);
};

/**
 * Initialise the more menus.
 *
 * @param {Object} menu The navbar moremenu.
 */
const init = menu => {
    autoCollapse(menu);
    window.addEventListener('resize', function () {
        autoCollapse(menu);
    });
};

export default {
    init: init,
};
