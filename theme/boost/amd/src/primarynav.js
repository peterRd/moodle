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
 * Toggling the visibility of the primary navigation on mobile.
 *
 * @package    theme_boost
 * @copyright  2021 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {classUtil} from 'core/utils';
import ModalBackdrop from 'core/modal_backdrop';
import Templates from 'core/templates';

let backdropPromise = 0;

const getBackdrop = () => {
    if (!backdropPromise) {
        backdropPromise = Templates.render('core/modal_backdrop', {})
            .then(function(html) {
                return new ModalBackdrop(html);
            })
            .fail(Notification.exception);
    }
    return backdropPromise;
};

const sel = {
    ids: {
        togglebutton: '#primarynavtoggle',
        pagecontent: '#page-wrapper',
        closebutton: '#primarynavclose'
    },
    classes: {
        dropdown: '.dropdown-menu'
    },
    regions: {
        languagemenu: '[data-region="languagemenu"] .dropdown-menu',
        languagemenucopy: '[data-region="languagemenucopy"]',
        usermenu: '[data-region="usermenu"] .dropdown-menu',
        usermenucopy: '[data-region="usermenucopy"]',
        primarynav: '[data-region="primarynavigation"]'
    }
};

const closeNav = () => {
    const primaryNav = document.querySelector(sel.regions.primarynav);
    const pageContent = document.querySelector(sel.ids.pagecontent);

    classUtil('remove', primaryNav, 'show');
    getBackdrop().then(backdrop => {
        backdrop.hide();
        pageContent.style.overflow = 'auto';
    });
};

const showNav = () => {
    const primaryNav = document.querySelector(sel.regions.primarynav);
    const primaryNavMenu = primaryNav.querySelector('.navbar-nav');
    const pageContent = document.querySelector(sel.ids.pagecontent);
    const userMenuCopy = primaryNav.querySelector(sel.regions.usermenucopy);
    const languageMenuCopy = primaryNav.querySelector(sel.regions.languagemenucopy);

    // Move the usermenu content into the primary navigation
    const userMenu = document.querySelector(sel.regions.usermenu);
    if (userMenu && !userMenuCopy) {
        const userContent = userMenu.innerHTML;
        Templates.render('core/collapse', {content: userContent, name: 'My profile'})
            .then(function(html) {
                const container = document.createElement("li");
                container.setAttribute('data-region', 'usermenucopy');
                container.innerHTML = html;
                container.querySelectorAll('.dropdown-divider').forEach((item) => {
                    item.classList.add('d-none');
                });
                container.querySelectorAll('.icon').forEach((item) => {
                    item.classList.add('d-none');
                });
                container.querySelectorAll('.dropdown-item').forEach((item) => {
                    classUtil('replace', item, 'dropdown-item', 'nav-link');
                });
                primaryNavMenu.appendChild(container);
            })
            .fail(Notification.exception);
    }

    // Move the languagemenu content into the primary navigation
    const languageMenu = document.querySelector(sel.regions.languagemenu);
    if (languageMenu && !languageMenuCopy) {
        const langContent = languageMenu.innerHTML;
        Templates.render('core/collapse', {content: langContent, name: 'Language'})
            .then(function(html) {
                const container = document.createElement("li");
                container.innerHTML = html;
                container.setAttribute('data-region', 'languagemenucopy');
                container.querySelectorAll('.dropdown-item').forEach((item) => {
                    classUtil('replace', item, 'dropdown-item', 'nav-link');
                });
                primaryNavMenu.appendChild(container);
            })
            .fail(Notification.exception);
    }


    classUtil('add', primaryNav, 'show');
    getBackdrop().then(backdrop => {
        backdrop.setZIndex(1020);
        backdrop.show();
        pageContent.style.overflow = 'hidden';
    });
};

export const togglenavigation = () => {
    const toggleButton = document.querySelector(sel.ids.togglebutton);
    const closeButton = document.querySelector(sel.ids.closebutton);
    const primarynav = document.querySelector(sel.regions.primarynav);

    if (toggleButton && closeButton) {
        toggleButton.addEventListener('click', () => {
            if (classUtil('has', primarynav, 'show')) {
                closeNav();
            } else {
                showNav();
            }
        });
    }
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            closeNav();
        });
    }

    window.addEventListener('resize', function () {
        if (window.outerWidth > 992) {
            closeNav();
        }
    });
};
