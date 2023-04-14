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
const questionId = parseInt(document.getElementById('questionId').value);
const endMessage = document.getElementById('allcluesdisplayed');
const timeLimitInMilliseconds = timeLimitInMinutes * 60 * 1000;

// Get clue count from HTML
const clueCount = parseInt(document.getElementById('clueCount').value);
let cluesToShow = parseInt(document.getElementById('cluesToShow').value);

if(cluesToShow >= clueCount){
    document.getElementById("timer").innerHTML = endMessage.value;
}
else{
    const timerInterval = setInterval(updateTimer, 100);
}

function updateTimer() {
    const now = new Date().getTime();
    const remaining = timerEnds - now;

    if(remaining > 0) {
        displayTimer(remaining);

    } else {
        document.getElementById("refreshButton").style.display = "flex";
        clearInterval(timerInterval);
    }
}

function displayTimer(remainingMilliseconds) {
    const hours = Math.floor((remainingMilliseconds || timeLimitInMilliseconds) / (1000 * 60 * 60));
    const minutes = Math.floor((remainingMilliseconds || timeLimitInMilliseconds) % (1000 * 60 * 60) / (1000 * 60));
    const seconds = Math.floor(((remainingMilliseconds || timeLimitInMilliseconds) % (1000 * 60)) / 1000);
    document.getElementById("timer").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
    document.getElementById("timer").style.display = "block";
}