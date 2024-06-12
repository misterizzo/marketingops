document.addEventListener( 'DOMContentLoaded', function () {

	// Adds a listener to the "Download PDF" button on the HTML invoice.
	const button = document.querySelector( '.button.pdf' );
	if ( button ) {
		button.addEventListener( 'click', function () {
			ppress_do_html2pdf( document.body, button.getAttribute( 'data-name' ) );
		} );
	}
} );

/**
 * Initializes `html2pdf` to create and save the PDF file.
 * @param {string} source   The data/HTML for the PDF.
 * @param {string} filename The filename to use for saving the PDF.
 */
function ppress_do_html2pdf ( source, filename ) {
	html2pdf( source, {
		margin: 0,
		filename: filename,
		image: { type: 'jpeg', quality: 0.98 },
		html2canvas: { scale: 2 },
		jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
	} );
}
