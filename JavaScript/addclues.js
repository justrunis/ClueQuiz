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

var addClueBtn = document.querySelector('#add-clue');
var removeBtn = document.querySelectorAll('.remove-clue');
var clueContainer = document.querySelector('#clues-container');
var clueIndex =  parseInt(document.getElementById('clue_index').value) - 1;
var removeString = parseInt(document.getElementById('remove_string').value);
console.log(clueIndex);


removeBtn.forEach(x => {
    x.addEventListener('click', function() {
        x.parentNode.parentNode.remove();
    });
});

addClueBtn.addEventListener('click', function(e) {
    e.preventDefault();
    clueIndex++;

    // Create the clue field
    var clueField = document.createElement('div');
    clueField.classList.add('form-group', 'row');

    // Create the hidden field for the clue interval
    var clueIntervalInput = document.createElement('input');
    clueIntervalInput.setAttribute('type', 'hidden');
    clueIntervalInput.setAttribute('name', 'clue[' + clueIndex + '][clue_interval]');
    clueIntervalInput.setAttribute('value', clueIndex);
    clueField.appendChild(clueIntervalInput);

    var clueLabel = document.createElement('label');
    clueLabel.setAttribute('for', 'id_clue' + clueIndex);
    clueLabel.classList.add('col-md-3', 'col-form-label', 'd-flex', 'pb-0', 'pr-md-0');
    const clueLabelString = document.getElementById('clueLabelString').value;
    const clueLabel = document.createElement('label');
    clueLabel.innerHTML = clueLabelString + clueIndex;

    clueField.appendChild(clueLabel);

    var clueInput = document.createElement('div');
    clueInput.classList.add('col-md-9');

    var textarea = document.createElement('textarea');
    textarea.setAttribute('name', 'clue[' + clueIndex + '][clue_text]');
    textarea.setAttribute('id', 'id_clue' + clueIndex);
    textarea.setAttribute('class', 'form-control');
    clueInput.appendChild(textarea);

    var removeBtn = document.createElement('button');
    removeBtn.classList.add('btn', 'btn-danger', 'mt-2');
    removeBtn.setAttribute('type', 'button');
    removeBtn.setAttribute('id', 'remove-clue-' + clueIndex);
    removeBtn.innerHTML = removeString;
    removeBtn.addEventListener('click', function() {
        clueField.remove();
    });
    clueInput.appendChild(removeBtn);

    clueField.appendChild(clueInput);
    clueContainer.appendChild(clueField);
});