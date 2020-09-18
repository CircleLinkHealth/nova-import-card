import excel from 'node-excel-export'

export const styles = {
    headerDark: {
      fill: {
        fgColor: {
          rgb: 'FF000000'
        }
      },
      font: {
        color: {
          rgb: 'FFFFFFFF'
        },
        sz: 14,
        bold: true,
        underline: true
      }
    },
    cellPink: {
      fill: {
        fgColor: {
          rgb: 'FFFFCCFF'
        }
      }
    },
    cellGreen: {
      fill: {
        fgColor: {
          rgb: 'FF00FF00'
        }
      }
    },
    cellNormal: {
      fill: {
        fgColor: {
          rgb: '0170b5FF'
        }
      },
      font: {
          color: {
              rgb: 'FFFFFFFF'
          }
      }
    }
  }

/**
 * @param {Object[]} sheets represents multiple report sheets
 * @param {String} sheets[].name specify sheet name
 * @param {Array[]} sheets[].heading raw heading array (optional)
 * @param {Object[]} sheets[].merges merge cell ranges
 * @param {Object} sheets[].specification report specification
 * @param {Object[]} sheets[].data sheet data
 * @returns {Buffer} 
 */
export default (sheets) => {
    if (!Array.isArray(sheets)) throw new Error('typeof sheets must be Array')
    return excel.buildExport(sheets)
}