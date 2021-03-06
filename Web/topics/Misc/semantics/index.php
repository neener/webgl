﻿<!DOCTYPE html>
<!-- 
  Générateur de noms aristocratiques
 -->
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Aristocroute</title>

<link rel="stylesheet" type="text/css" href="/css/jquery-ui-themes-1.9.2/themes/base/jquery.ui.all.css"/>
<link rel="stylesheet" type="text/css" href="/css/layout-default-1.3.0.css"/>
<link rel="stylesheet" type="text/css" href="style.css"/>

<!-- jQuery + plugs -->
<script type="text/javascript" src="/js/jquery-1.8.3.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="/js/jquery.layout-1.3.0.js"></script>

<!-- patapi -->
<script type="text/javascript" src="../../WebGL/o3djs/base.js"></script>			<!-- Include a small part of the O3D library to help us with includes-->
<script type="text/javascript" src="../../WebGL/patapi/base.js"></script>			<!-- Include PatAPI-->

<script type="text/javascript">
o3djs.require( 'patapi' );
o3djs.require( 'patapi.ui' );

var	gender = -1;						// Invalid so it gets rebuilt at once when we call SetGender()
var	comboSelectedTitleIndex = 0;		// Random
var	firstNamesCount = 2;
var	particlesCount = 3;


var	database;	// JSON database loaded there

// Nobility title
function	OnComboChange( _select )
{
	comboSelectedTitleIndex = parseInt( _select.options[_select.selectedIndex].value );
	Rebuild();
}

$(document).ready(
function()
{
	// Load JSON database
	try
	{
		var	FileContent = patapi.helpers.LoadFileSynchronous( "database.json" );
		database = eval( '(' + FileContent + ')' );
	}
	catch ( _e )
	{
		alert( "Erreur pendant le parsing de la database : " + _e );
		return;
	}

	// Gender selection
	$( '#Gender_Male' ).click( function( e ) { SetGender( 0 ); } );
	$( '#Gender_Female' ).click( function( e ) { SetGender( 1 ); } );
	SetGender( 0 );		// Call SetGender() to build the combo box at least once

	// First names
	$( '#PrenomTextBox' ).change( Rebuild );
	$( '#PrenomTextBox' ).keyup( Rebuild );

	function	OnChange0( event, ui )
	{
		firstNamesCount = ui.value;
		Rebuild();
	}
	$('#PrenomSlider').slider( {
			range: false,
			min: 0,
			max : 10,
			step: 1,
			value: firstNamesCount,
			change: OnChange0,
			slide : OnChange0,
		} );

	// Particules
	function	OnChange1( event, ui )
	{
		particlesCount = ui.value;
		Rebuild();
	}
	$('#ParticulesSlider').slider( {
			range: false,
			min: 1,
			max : 10,
			step: 1,
			value: particlesCount,
			change: OnChange1,
			slide : OnChange1,
		} );

	// Regen button
	$( '#Regen' ).button().click( Regen );
	Regen();
} );

$(window).unload( function()
{

} );

function	SetGender( _gender )
{
	if ( _gender == gender )
		return;	// No change

	gender = _gender;

	// Rebuild combo box
	var	combobox = $('#ListTitreDeNoblesse');
		combobox[0].innerHTML = '';	// Clear content

	// Append "Random" and "None" choices
	combobox.append( '<option value="0">Aléatoire</option>' );
	combobox.append( '<option value="1">Aucun</option>' );

	// Append standard titles
	var	SourceArray = _gender == 0 ? database.Male.Titles : database.Female.Titles;
	for ( var TitleIndex=0; TitleIndex < SourceArray.length; TitleIndex++ )
	{
		combobox.append( '<option value="' + (2+TitleIndex) + '">' + SourceArray[TitleIndex] + '</option>' );
	}

	// Select the last index
	combobox[0].value = comboSelectedTitleIndex.toString();

	// Regenerate random values
	Regen();
}

// Regenerate random values
var	randomTitleIndex = 0;
var	randomFirstNames = [];
var	randomNames = [];

function	Regen()
{
	if ( !database )
		return;

	// Draw another random title...
	randomTitleIndex = RNG( database.Male.Titles.length );

	// Draw 10 possible first names
	randomFirstNames = [];
	{
		var	SourceArray = gender == 0 ? database.Male.FirstNames : database.Female.FirstNames;
			SourceArray = SourceArray.slice(0);	// Copy the array
		for ( var i=0; i < 10; i++ )
		{
			// Append random name
			var	RandomNameIndex = RNG( SourceArray.length );
			var	Name = SourceArray[RandomNameIndex];
			randomFirstNames.push( Name );

			// Remove that name so it doesn't get drawn again...
			SourceArray.splice( RandomNameIndex, 1 );
		}
	}

	// Draw 10 possible particles
	randomNames = [];
	{
		var	SourceArray0 = database.Male.Particles;
		var	SourceArray1 = database.Female.Particles;
			SourceArray0 = SourceArray0.slice(0);	// Copy the array
			SourceArray1 = SourceArray1.slice(0);	// Copy the array

		for ( var i=0; i < 10; i++ )
		{
			// Append random name
			var	RandomNameIndex = RNG( SourceArray0.length );
			var	Name = SourceArray0[RandomNameIndex];
			randomNames.push( Name );

			// Remove that name so it doesn't get drawn again...
			SourceArray0.splice( RandomNameIndex, 1 );

			// Swap arrays to alternate between male and female particles
			var	Temp = SourceArray0;
			SourceArray0 = SourceArray1;
			SourceArray1 = Temp;
		}
	}

	// Refresh
	Rebuild();
}
function	RNG( a, b )
{
	if ( a === undefined )
	{	// No bound specified
		a = 0;
		b = 1;
	}
	else if ( b === undefined )
	{	// Assuming a count is specified
		b = a-1;
		a = 0;
	}

	var	r = Math.random();
		r = Math.floor( a + (b-a) * r );
		r = Math.min( b, r );	// Make sure we're never over max bound (occurs if random is exactly 1)

	return r;
}

function	Rebuild()
{
	var	Result = "<em>";

	// First, update sliders' labels
	$( '#PrenomsLabel' ).text( "Autres Prénoms (" + firstNamesCount.toFixed() + ")" );
	$( '#ParticulesLabel' ).text( "Nombre de particules (" + particlesCount.toFixed() + ")" );

	// Build title
	if ( comboSelectedTitleIndex != 1 )
	{
		var	SourceArray = gender == 0 ? database.Male.Titles : database.Female.Titles;
		var	TitleIndex = comboSelectedTitleIndex == 0 ? randomTitleIndex : comboSelectedTitleIndex-2;

		Result += SourceArray[TitleIndex] + " ";
	}

	// Retrieve first name from the textbox and strip it of any spaces
//	Result += "<em>";

	var	FirstName = $( '#PrenomTextBox' )[0].value;
		FirstName = FirstName.trim();

	Result += FirstName + " ";

	// Append additional first names
	for ( var i=0; i < firstNamesCount; i++ )
	{
		Result += randomFirstNames[i] + " ";
	}

	Result += "</em>";

	// Generate particles
	if ( particlesCount > 0 )
	{
		Result += '<strong>';

		for ( var i=0; i < particlesCount; i++ )
		{
			Result += (i > 0 && i == particlesCount-1 ? "et " : "") + randomNames[i] + " ";
		}

		Result += '</strong>';
	}

	// Display result
	$( '#ResultBox' )[0].innerHTML = Result;
}

</script>
</head>

<body oncontextmenu="return false;" style="text-align: center; font-family:Trebuchet MS;">

	<div id="TitleTop" class="title">
		<h1>Générateur de noms aristocratiques</h1>
	</div>

	<div id="Options" class="options-container">

		<!-- Homme/Femme -->
		<div class='gender-container'>
			<div class='gender'><input id="Gender_Male" value="0" name="radioGender" type="radio" class='t0' checked="checked"><label for="Gender_Male" class='t1'>Homme</label></input></div>
			<div class='gender'><input id="Gender_Female" value="1" name="radioGender" type="radio" class='t0'><label for="Gender_Female" class='t1'>Femme</label></input></div>
		</div>

		<!-- Titre de noblesse -->
		<div id="Titre" class="nobility-container">
			<div class="t0">Titre de noblesse</div>
			<div class="t1">
				<select id="ListTitreDeNoblesse" onchange='OnComboChange( this )'>
				</select>
			</div>
		</div>

		<!-- Prénom -->
		<div id="Prenom" class='first-name-container'>
			<div class='first-name-option'>
				<div class='t0'>Prénom</div>							<div class='t1'><input id='PrenomTextBox' type="text" value="Guy Gontran" for="FirstName_Input" style='margin-left:10px;'></div>
			</div>
			<div class='first-name-option'>
				<div class='t0' id="PrenomsLabel">Autres Prénoms</div>	<div id="PrenomSlider" class='t1'></div>
			</div>

				<!-- Aléatoire
				<div class='first-name-option'>
					<div class='first-name-option-line'><input id="FirstName_Random" value="0" name="radioFirstName" type="radio" class='checkbox' checked="checked"><label for="FirstName_Random" class='t1'>Aléatoire</label></div>
					<div class='first-name-option-line'><label class='t0' style='margin-left:40px;'>Nombre de prénoms</label><div id="PrenomSlider" class='t1'></div></div>
				</div>

				<!-- Manuel
				<div class='first-name-option'>
					<div class='first-name-option-line'>
						<input id="FirstName_Manual" value="1" name="radioFirstName" type="radio" class='checkbox'"><label for="FirstName_Manual" class='t0'>Manuel</label>
					</div>
				</div>
			</div>
				 -->
		</div>

		<!-- Nombre de particules -->
		<div id="Particules" class="particles-container">
			<div class="particles-label"id="ParticulesLabel">Nombre de particules</div>		<div id="ParticulesSlider" class="particles-slider"></div>
		</div>

		<div class='regen-container'>
			<button id="Regen">Regénérer</button>
		</div>
	</div>

	<div id="ResultContainer" class="resultbox-container">
		<div class="resultbox-top">
			<div class="resultbox-nw"></div>
			<div class="resultbox-n"></div>
			<div class="resultbox-ne"></div>
		</div>
		<div class="resultbox-middle">
			<div class="resultbox-w"></div>
			<div class="resultbox"><div id="ResultBox">			Guy Gontran du Tertre de la Tour de Pron			</div></div>
			<div class="resultbox-e"></div>
		</div>
		<div class="resultbox-bottom">
			<div class="resultbox-sw"></div>
			<div class="resultbox-s"></div>
			<div class="resultbox-se"></div>
		</div>
	</div>
</body>
</html>
