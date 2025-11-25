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

		// Iterate over settings to find matching slug by label
		// We use the last selected unit (or first found) to determine values
		for (let i = 0; i < selectedUnits.length; i++) {
			const label = selectedUnits[i];
			
			// Find slug in pfadiSettings where label matches
			for (const slug in pfadiSettings) {
				if (pfadiSettings.hasOwnProperty(slug)) {
					if (pfadiSettings[slug].label === label) {
						greeting = pfadiSettings[slug].greeting;
						leaders = pfadiSettings[slug].leaders;
						starttime = pfadiSettings[slug].starttime;
						endtime = pfadiSettings[slug].endtime;
						break; 
					}
				}
			}
			// If we found values, break (prioritize first selected)
			if (greeting || leaders) {
				break;
			}
		}

		// Update Greeting and Leaders (Overwrite to ensure it updates)
		if (greeting) {
			$('#pfadi_greeting').val(greeting);
		}
		if (leaders) {
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
