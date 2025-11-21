jQuery(document).ready(function ($) {
    // Function to update fields based on selected terms
    function updateFields() {
        var selectedUnits = [];

        // Get checked checkboxes from the taxonomy meta box
        $('#activity_unitchecklist input:checked').each(function () {
            var label = $(this).parent().text().trim();
            selectedUnits.push(label);
        });

        if (selectedUnits.length === 0) {
            return;
        }

        var greeting = '';
        var leaders = '';

        // Check if 'Abteilung' is selected
        if (selectedUnits.includes('Abteilung')) {
            greeting = pfadiSettings['abteilung'].greeting;
            leaders = pfadiSettings['abteilung'].leaders;
        } else {
            // Use the first selected unit for now, or maybe combine them?
            // Spec says: "Wenn eine Stufe (z.B. "Wölfe") angeklickt wird... lädt die Werte"
            // Let's use the most recently clicked one effectively by iterating
            // But since we are iterating all checked, let's prioritize by hierarchy if needed or just take the last one
            // Actually, let's just take the first one found that isn't Abteilung if Abteilung isn't there.
            // Or better, let's trigger this on change event specifically.

            // However, we are inside updateFields which might be called on load too?
            // The spec says: "Event: Wenn eine Stufe (z.B. "Wölfe") angeklickt wird."

            // Let's simplify: iterate and find the corresponding slug
            // We need to map label to slug.
            // pfadiSettings keys are slugs.

            // Helper to find slug from label (simple lowercase/sanitize approximation)
            // Ideally we would have the slug in the data attribute but WP checklist gives us value=term_id
            // We rely on the label text which matches our hardcoded units.

            var unitSlug = selectedUnits[0].toLowerCase().replace(/ö/g, 'o').replace(/ä/g, 'a').replace(/ü/g, 'u'); // Very basic sanitization
            // Better: iterate through pfadiSettings keys and match?
            // Let's try to match the label.

            // Actually, let's just look at the last checked item if we want to be reactive to the click?
            // No, let's just loop and pick the first valid one.

            for (var i = 0; i < selectedUnits.length; i++) {
                var slug = selectedUnits[i].toLowerCase().replace(/ö/g, 'oe').replace(/ä/g, 'ae').replace(/ü/g, 'ue');
                // The sanitize_title in PHP does: Wölfe -> wolfe (default WP) or woelfe (german locale)?
                // Let's assume standard WP sanitize_title behavior for 'Wölfe' -> 'wolfe'
                if (slug === 'wolfe') slug = 'wolfe'; // wait, sanitize_title('Wölfe') is usually 'wolfe'

                // Let's try to match loosely
                if (pfadiSettings[slug]) {
                    greeting = pfadiSettings[slug].greeting;
                    leaders = pfadiSettings[slug].leaders;
                    break; // Found one
                }

                // Try 'wolfe' specifically for Wölfe
                if (selectedUnits[i] === 'Wölfe' && pfadiSettings['wolfe']) {
                    greeting = pfadiSettings['wolfe'].greeting;
                    leaders = pfadiSettings['wolfe'].leaders;
                    break;
                }
            }
        }

        // Only update if fields are empty or we want to overwrite?
        // Spec says: "Aktion: Das Script lädt die in 3.1 definierten Werte (Gruss & Leitung) und fügt sie in die Textfelder ein."
        // It implies overwriting or filling. Let's fill.

        if (greeting) $('#pfadi_greeting').val(greeting);
        if (leaders) $('#pfadi_leaders').val(leaders);
    }

    // Bind change event to checkboxes
    $('#activity_unitchecklist input[type="checkbox"]').on('change', function () {
        // If this specific checkbox was checked, we might want to prioritize its values
        // But the logic "Abteilung wins" must hold.
        updateFields();
    });
});
