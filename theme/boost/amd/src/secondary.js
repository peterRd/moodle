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
 *
 * @package    theme_boost
 * @copyright  2021 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

let prevScroll = 0;
let currentScroll = 0;

export const autohidenav = () => {
    const scroller = document.querySelector('#page');
    const nav = document.querySelector('.secondarynavcontainer');
    const watch = document.querySelector('#navwatch');

    scroller.addEventListener('scroll', function () {
        currentScroll = watch.offsetTop;
        if (currentScroll > 200) {
            if (currentScroll > prevScroll) {
                nav.classList.remove('sticky-top');
            } else {
                nav.classList.add('sticky-top');
            }
            prevScroll = currentScroll;
        }
    });

    window.addEventListener('resize', function () {
        nav.classList.remove('sticky-top');
    });
};
