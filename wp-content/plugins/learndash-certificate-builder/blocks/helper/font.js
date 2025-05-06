/**
 * Build the selector
 *
 * @param arr
 * @param font
 * @returns {{list: [], currFont: any}}
 */
export function getActiveFont(arr, font) {
    let list = [];
    let currFont = undefined
    if (font === '') {
        font = arr[0][0]
    }
    arr.forEach((v, i) => {
        let key = v[0];
        let info = v[1];
		list.push({
			label: info.name,
			value: key
		})
		if (key === font) {
			currFont = info;
			currFont.key = key;
		}
    })
    // the currentfont is empty, this can happen because
    if (currFont === undefined) {
        currFont = arr[0][0];
    }
    return {
        list: list,
        currFont: currFont
    }
}

/**
 * Build the font stack styles
 *
 * @param baseUrl
 * @param fonts
 * @returns {string}
 */
export function buildFontStyle(baseUrl, fonts) {
    let fontStyle = '';
    for (const [key, v] of Object.entries(fonts)) {
        let fontUrl;
        if (v.custom === true) {
            fontUrl = v['R']
        } else {
            fontUrl = baseUrl + '/' + v['R']
        }
        fontStyle += `
		@font-face {
			font-family: "${key}";
			src: url("${fontUrl}") format('truetype');
			font-weight: 400;
			font-style: normal
		}
	`
        if (v['B'] !== undefined) {
            let fontUrl_B = baseUrl + '/' + v['B']
            if (v.custom === true) {
                fontUrl_B = v['B'];
            }
            fontStyle += `
		@font-face {
			font-family: "${key}";
			src: url("${fontUrl_B}") format('truetype');
			font-weight: bold;
			font-style: normal
		}
		`
        }
        if (v['I'] !== undefined) {
            let fontUrl_I = baseUrl + '/' + v['I']
            if (v.custom === true) {
                fontUrl_I = v['I'];
            }
            fontStyle += `
		@font-face {
			font-family: "${key}";
			src: url("${fontUrl_I}") format('truetype');
			font-weight: 400;
			font-style: italic
		}
		`
        }
        if (v['BI'] !== undefined) {
            let fontUrl_BI = baseUrl + '/' + v['BI']
            if (v.custom === true) {
                fontUrl_BI = v['BI'];
            }
            fontStyle += `
		@font-face {
			font-family: "${key}";
			src: url("${fontUrl_BI}") format('truetype');
			font-weight: bold;
			font-style: italic
		}
		`
        }
    }

    return fontStyle;
}
