"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.CREATE_TABLE_COMMAND = `
CREATE TABLE IF NOT EXISTS \`ccdas_v2\` (
  \`id\` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  \`ccda_id\` int(11) NOT NULL,
  \`result\` json DEFAULT NULL,
  \`error\` varchar(255) DEFAULT NULL,
  \`status\` varchar(255) NOT NULL,
  \`created_at\` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  \`updated_at\` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (\`id\`),
  UNIQUE KEY \`id\` (\`id\`),
  UNIQUE KEY \`ccda_id_index\` (\`ccda_id\`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
`;
//# sourceMappingURL=db-create-table.js.map