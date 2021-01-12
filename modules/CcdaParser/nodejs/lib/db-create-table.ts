export function createTableCommand(tableName: string) {
    return `
CREATE TABLE IF NOT EXISTS \`${tableName}\` (
  \`id\` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  \`ccda_id\` int(11) NOT NULL,
  \`result\` json DEFAULT NULL,
  \`error\` varchar(255) DEFAULT NULL,
  \`status\` varchar(255) NOT NULL,
  \`duration_seconds\` int(11) NOT NULL DEFAULT 0,
  \`created_at\` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  \`updated_at\` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (\`id\`),
  UNIQUE KEY \`id\` (\`id\`),
  UNIQUE KEY \`ccda_id_index\` (\`ccda_id\`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
`;
}