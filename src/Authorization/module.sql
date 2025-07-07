
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `key` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `level` tinyint(1) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'admin', 'Admin', 9);
INSERT INTO `roles` VALUES ('c87e615c-dd9c-4ecd-bcd7-de38dac2f39f', 'user', 'User', 1);

-- ----------------------------
-- Table structure for user_roles
-- ----------------------------
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles`  (
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`) USING BTREE,
  INDEX `fk_role_to_roles`(`role_id` ASC) USING BTREE,
  CONSTRAINT `fk_user_roles_to_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_roles_to_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_roles
-- ----------------------------
INSERT INTO `user_roles` VALUES ('c13e550a-60ee-48d5-bf6e-ed29310640b2', '6be6178d-fe99-47b6-90d5-2a0c4d25b6dc');

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions`  (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `module` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `action` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `route` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of permissions
-- ----------------------------


INSERT INTO `permissions` VALUES ('10e5a2e3-9c81-44a5-8c4f-4522ef11df5b', 'Users', 'Users', 'delete', '/api/users/delete/:userId', 'DELETE');
INSERT INTO `permissions` VALUES ('2ecb0afa-5802-4a81-92c9-72c311d9fec4', 'Users', 'Users', 'edit', '/api/users/updatePassword/:userId', 'PUT');
INSERT INTO `permissions` VALUES ('4489a158-8d19-4100-adaa-e1de137fc4b3', 'Users', 'Users', 'list', '/api/users/findAllByPaging', 'GET');
INSERT INTO `permissions` VALUES ('81a4e936-0ca9-46c0-a4c2-f757ffa9a705', 'Users', 'Users', 'show', '/api/users/findAllByPaging', 'GET');
INSERT INTO `permissions` VALUES ('861aa1d3-fd95-401f-9666-d2616eb43eb8', 'Users', 'Users', NULL, '/api/users/findAll', 'GET');
INSERT INTO `permissions` VALUES ('a7b0b4a2-4504-46d8-88c8-0a4c49f23b07', 'Users', 'Users', NULL, '/api/users/findOneById/:userId', 'GET');
INSERT INTO `permissions` VALUES ('ae28c04b-07b7-486d-b782-6b5c0e93ab01', 'Users', 'Users', 'edit', '/api/users/update/:userId', 'PUT');
INSERT INTO `permissions` VALUES ('c99e90cd-0403-4a4e-aa8c-b6d21dc38e96', 'Users', 'Users', 'create', '/api/users/create', 'POST');
INSERT INTO `permissions` VALUES ('a8b787bc-0652-4b13-b24c-7cc9ed565b13', 'Users', 'Roles', 'list', '/api/users/roles/findAll', 'GET');
INSERT INTO `permissions` VALUES ('7f7320be-7882-4ae4-bdd1-1a577b9fecf8', 'Users', 'My Account', 'list', '/api/users/myAccount/findMe', 'GET');
INSERT INTO `permissions` VALUES ('f7ff6d01-a40c-4cbf-9501-840153120764', 'Users', 'My Account', 'edit', '/api/users/myAccount/updatePassword', 'PUT');
INSERT INTO `permissions` VALUES ('ee3c41ff-aa4b-48de-86cd-bf1590bbc275', 'Users', 'My Account', 'edit', '/api/users/myAccount/update', 'PUT');

INSERT INTO `permissions` VALUES ('2d059610-9351-4d1e-b2e2-035bdc353b64', 'Modules', 'Modules', 'list', '/api/modules/findAllByPaging', 'GET');
INSERT INTO `permissions` VALUES ('496ef5b5-e39d-4ece-baab-82fe18b632c3', 'Modules', 'Modules', 'list', '/api/modules/findAll', 'GET');
INSERT INTO `permissions` VALUES ('4aeaefad-a4bd-4273-9d92-7908471f95b4', 'Modules', 'Modules', 'create', '/api/modules/update/:moduleId', 'PUT');
INSERT INTO `permissions` VALUES ('6cb2bea6-2e6f-4135-86e5-e6249779c49a', 'Modules', 'Modules', 'create', '/api/modules/create', 'POST');
INSERT INTO `permissions` VALUES ('9951e57b-b2a7-4ab3-8443-4649add1e079', 'Modules', 'Modules', 'delete', '/api/modules/delete/:moduleId', 'DELETE');

INSERT INTO `permissions` VALUES ('6566781a-267e-46d6-91c7-d92d46c9d501', 'Authorization', 'Permissions', 'list', '/api/authorization/permissions/findAllByPaging', 'GET');
INSERT INTO `permissions` VALUES ('e7a2a41b-dec3-48bb-911d-d8adab449105', 'Authorization', 'Permissions', 'edit', '/api/authorization/permissions/update/:permId', 'PUT');
INSERT INTO `permissions` VALUES ('f03cb2aa-1a5c-495b-aa05-2809da2f2bbe', 'Authorization', 'Permissions', NULL, '/api/authorization/permissions/findOneById', 'GET');
INSERT INTO `permissions` VALUES ('8b336350-9ed7-4eee-94b1-d335b33fff2a', 'Authorization', 'Permissions', 'delete', '/api/authorization/permissions/delete/:permId', 'DELETE');
INSERT INTO `permissions` VALUES ('9f0e7359-021e-4853-b577-7a1b174794e9', 'Authorization', 'Permissions', 'create', '/api/authorization/permissions/copy/:permId', 'POST');
INSERT INTO `permissions` VALUES ('bf077931-9467-434e-afe8-8cb531187280', 'Authorization', 'Permissions', 'create', '/api/authorization/permissions/create', 'POST');
INSERT INTO `permissions` VALUES ('5401297e-a971-4df2-a550-7cde1455a3e4', 'Authorization', 'Roles', NULL, '/api/authorization/roles/findAll', 'GET');
INSERT INTO `permissions` VALUES ('56075480-5a2a-4597-988b-76fe53eb7eae', 'Authorization', 'Roles', 'create', '/api/authorization/roles/create', 'POST');
INSERT INTO `permissions` VALUES ('5a210c0f-3034-4aa8-a592-dd002cdd4c04', 'Authorization', 'Roles', 'list', '/api/authorization/roles/findAllByPaging', 'GET');
INSERT INTO `permissions` VALUES ('119a2ce9-c938-4a59-8ee2-72bf4bead996', 'Authorization', 'Roles', NULL, '/api/authorization/roles/findOneById/:roleId', 'GET');
INSERT INTO `permissions` VALUES ('e55d7246-7d38-4c6c-b347-0754bb88a06c', 'Authorization', 'Roles', 'delete', '/api/authorization/roles/delete/:roleId', 'DELETE');
INSERT INTO `permissions` VALUES ('8d14368f-3899-4d54-9948-fda03a4e2f87', 'Authorization', 'Roles', 'edit', '/api/authorization/roles/update/:roleId', 'PUT');
INSERT INTO `permissions` VALUES ('05e6c960-6e20-4b69-8bde-87340388f072', 'Authorization', 'UserRoles', 'edit', '/api/authorization/userRoles/assign', 'PUT');
INSERT INTO `permissions` VALUES ('65d2b3ea-f392-4992-b2ef-729d2af8356f', 'Authorization', 'UserRoles', 'list', '/api/authorization/user_roles/findAllByPaging/:roleId', 'GET');
INSERT INTO `permissions` VALUES ('ad138dd4-4694-4494-a5f7-efcbc7610163', 'Authorization', 'UserRoles', 'edit', '/api/authorization/user_roles/unassign', 'PUT');

-- ----------------------------
-- Table structure for role_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions`  (
  `role_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `perm_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`role_id`, `permId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of role_permissions
-- ----------------------------
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '05e6c960-6e20-4b69-8bde-87340388f072');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '0aad9252-395d-4c18-a6b0-90a7625cf423');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '10e5a2e3-9c81-44a5-8c4f-4522ef11df5b');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '119a2ce9-c938-4a59-8ee2-72bf4bead996');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '1fafaa3f-f28c-429f-8dcc-dcc5bac7bc02');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '20ac4874-a820-4767-badc-be5b94d196ae');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '2d059610-9351-4d1e-b2e2-035bdc353b64');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '2ecb0afa-5802-4a81-92c9-72c311d9fec4');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '383390d1-6d27-40da-949e-048426c6bd83');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '4489a158-8d19-4100-adaa-e1de137fc4b3');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '496ef5b5-e39d-4ece-baab-82fe18b632c3');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '4aeaefad-a4bd-4273-9d92-7908471f95b4');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '5401297e-a971-4df2-a550-7cde1455a3e4');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '56075480-5a2a-4597-988b-76fe53eb7eae');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '5a210c0f-3034-4aa8-a592-dd002cdd4c04');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '6566781a-267e-46d6-91c7-d92d46c9d501');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '65d2b3ea-f392-4992-b2ef-729d2af8356f');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '6cb2bea6-2e6f-4135-86e5-e6249779c49a');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '7f7320be-7882-4ae4-bdd1-1a577b9fecf8');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '81a4e936-0ca9-46c0-a4c2-f757ffa9a705');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '861aa1d3-fd95-401f-9666-d2616eb43eb8');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '869cbad6-a276-4de7-85a5-244fe8d8df2d');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '8b336350-9ed7-4eee-94b1-d335b33fff2a');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '8d14368f-3899-4d54-9948-fda03a4e2f87');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '9951e57b-b2a7-4ab3-8443-4649add1e079');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '9d5c898a-25bd-47c9-b3de-c9f67a357ecb');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', '9f0e7359-021e-4853-b577-7a1b174794e9');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'a0c1c61d-8591-4bfb-a467-2407da6001ac');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'a7b0b4a2-4504-46d8-88c8-0a4c49f23b07');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'a8b787bc-0652-4b13-b24c-7cc9ed565b13');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'ad138dd4-4694-4494-a5f7-efcbc7610163');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'ae28c04b-07b7-486d-b782-6b5c0e93ab01');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'bf077931-9467-434e-afe8-8cb531187280');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'c99e90cd-0403-4a4e-aa8c-b6d21dc38e96');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'e1fcfe44-67d4-4818-92ac-8a3303f8658c');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'e55d7246-7d38-4c6c-b347-0754bb88a06c');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'e7a2a41b-dec3-48bb-911d-d8adab449105');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'ee3c41ff-aa4b-48de-86cd-bf1590bbc275');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'f03cb2aa-1a5c-495b-aa05-2809da2f2bbe');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'f0bfa5b4-2ee8-46e3-8379-5604a1db4d86');
INSERT INTO `role_permissions` VALUES ('6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'f7ff6d01-a40c-4cbf-9501-840153120764');
INSERT INTO `role_permissions` VALUES ('9d548023-899f-461e-bd45-c925a66499ee', '0aad9252-395d-4c18-a6b0-90a7625cf423');
INSERT INTO `role_permissions` VALUES ('9d548023-899f-461e-bd45-c925a66499ee', '383390d1-6d27-40da-949e-048426c6bd83');
INSERT INTO `role_permissions` VALUES ('9d548023-899f-461e-bd45-c925a66499ee', 'a0c1c61d-8591-4bfb-a467-2407da6001ac');
INSERT INTO `role_permissions` VALUES ('9d548023-899f-461e-bd45-c925a66499ee', 'e1fcfe44-67d4-4818-92ac-8a3303f8658c');

SET FOREIGN_KEY_CHECKS = 1;