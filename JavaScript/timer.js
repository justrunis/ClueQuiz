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
 * @module    mod_cluehunt/JavaScript
 * @package   mod_cluehunt
 * @copyright Justinas Runevičius <justinas.runevicius@distance.ktu.lt>
 * @author Justinas Runevičius <justinas.runevicius@distance.ktu.lt>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get time limit from PHP and convert to milliseconds
const timeLimitInMinutes = parseInt(document.getElementById('timeLimit').value);
const timeLimitInMilliseconds = timeLimitInMinutes * 60 * 1000;

const allClues = document.querySelectorAll("#clues > p");

// Get clue count from PHP
const clueCount = parseInt(document.getElementById('clueCount').value);

let startTime = localStorage.getItem('startTime');
if(!startTime){
    // Start timer
    startTime = new Date().getTime();
    localStorage.setItem('startTime', startTime);
}

const timerInterval = setInterval(updateTimer, 100);

function updateTimer() {
    const now = new Date().getTime();
    const elapsedMilliseconds = now - startTime;
    const remainingMilliseconds = timeLimitInMilliseconds - (elapsedMilliseconds % timeLimitInMilliseconds);

    const temp = Math.floor(elapsedMilliseconds / timeLimitInMilliseconds)
    const remainingClues = clueCount - temp;

    for (let i = 0; i < Math.min(clueCount, temp); i++) {
        allClues[i].style.display = 'block';
    }
    if (remainingClues >= 1) {
        displayTimer(remainingMilliseconds);
    } else {
        // Display final message
        document.getElementById("timer").innerHTML = "All clues are displayed";
        clearInterval(timerInterval);
    }
}

function displayTimer(remainingMilliseconds) {
    const hours = Math.floor((remainingMilliseconds || timeLimitInMilliseconds) / (1000 * 60 * 60));
    const minutes = Math.floor((remainingMilliseconds || timeLimitInMilliseconds) % (1000 * 60 * 60) / (1000 * 60));
    const seconds = Math.floor(((remainingMilliseconds || timeLimitInMilliseconds) % (1000 * 60)) / 1000);
    // console.error( remainingMilliseconds);
    document.getElementById("timer").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
    document.getElementById("timer").style.display = "block";
}