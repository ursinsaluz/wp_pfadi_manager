jQuery(document).ready(function ($) {
	// Function to update fields based on selected terms
	function updateFields() {
		const selectedUnits = [];

		// Get checked checkboxes from the taxonomy meta box
		$('#activity_unitchecklist input:checked').each(function () {
			const label = $(this).parent().text().trim();
			selectedUnits.push(label);
		});

		if (selectedUnits.length === 0) {
			return;
		}

		let greeting = '';
		let leaders = '';
		let starttime = '';
		let endtime = '';

		// Check if 'Abteilung' is selected
		if (selectedUnits.includes('Abteilung')) {
			greeting = pfadiSettings.abteilung.greeting;
			leaders = pfadiSettings.abteilung.leaders;
		} else {
			// Use the first selected unit for now, or maybe combine them?
			// Spec says: "Wenn eine Stufe (z.B. "Wölfe") angeklickt wird... lädt die Werte"
			// Let's use the most recently clicked one effectively by iterating
			// But since we are iterating all checked, let's prioritize by hierarchy if needed or just take the last one
			// Actually, let's just take the first one found that isn't Abteilung if Abteilung isn't there.
			// Or better, let's trigger this on change event specifically.

			// However, we are inside updateFields which might be called on load too?
			// The spec says: "Event: Wenn eine Stufe (z.B. "Wölfe") angeklickt wird."

			// Map labels to slugs
			const slugMap = {
				Biber: 'biber',
				Wölfe: 'wolfe',
				Pfadis: 'pfadis',
				Pios: 'pios',
				Rover: 'rover',
				Abteilung: 'abteilung',
			};

			for (let i = 0; i < selectedUnits.length; i++) {
				const label = selectedUnits[i];
				const slug = slugMap[label];

				if (slug && pfadiSettings[slug]) {
					greeting = pfadiSettings[slug].greeting;
					leaders = pfadiSettings[slug].leaders;
					starttime = pfadiSettings[slug].starttime;
					endtime = pfadiSettings[slug].endtime;
					break; // Take the first valid one found
				}
			}
		}

		// Only update if fields are empty or we want to overwrite?
		// Spec says: "Aktion: Das Script lädt die in 3.1 definierten Werte (Gruss & Leitung) und fügt sie in die Textfelder ein."
		// It implies overwriting or filling. Let's fill.

		// Update Greeting and Leaders if empty
		if (greeting && !$('#pfadi_greeting').val()) {
			$('#pfadi_greeting').val(greeting);
		}
		if (leaders && !$('#pfadi_leaders').val()) {
			$('#pfadi_leaders').val(leaders);
		}

		// Update Times (preserve date)
		if (starttime) {
			const currentStart = $('#pfadi_start_time').val(); // YYYY-MM-DDTHH:mm
			if (currentStart && currentStart.indexOf('T') > -1) {
				const parts = currentStart.split('T');
				$('#pfadi_start_time').val(parts[0] + 'T' + starttime);
			}
		}
		if (endtime) {
			const currentEnd = $('#pfadi_end_time').val();
			if (currentEnd && currentEnd.indexOf('T') > -1) {
				const parts = currentEnd.split('T');
				$('#pfadi_end_time').val(parts[0] + 'T' + endtime);
			}
		}
	}

	// Function to toggle visibility of details meta box
	function toggleDetailsBox() {
		const hasSelection =
			$('#activity_unitchecklist input:checked').length > 0;
		const detailsBox = $(
			'#pfadi_activity_details, #pfadi_announcement_details'
		);

		if (hasSelection) {
			detailsBox.slideDown();
		} else {
			detailsBox.hide();
		}
	}

	// Initial check
	toggleDetailsBox();

	// Bind change event to checkboxes
	$('#activity_unitchecklist input[type="checkbox"]').on(
		'change',
		function () {
			toggleDetailsBox();
			updateFields();
		}
	);
});
