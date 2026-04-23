var app = new Vue({
    el: '#app',
    data: USER_DATA,
    watch: {
        selectedRole(newRole) {
            this.updatePermissions(newRole);
        }
    },
    methods: {
        setActiveRole(role) {
            this.selectedRole = role;
            // Khi chọn role mới, reset về permissions mặc định của role đó
            if (!this.isEditMode || confirm('Switching role will reset permissions to default. Continue?')) {
                this.isEditMode = false; // Tắt edit mode để load permissions mặc định
                this.updatePermissions(role);
            }
        },
        
        getRoleIcon(role) {
            const icons = {
                'admin': 'fas fa-crown',
                'moderator': 'fas fa-user-shield',
                'author': 'fas fa-pen-fancy',
                'member': 'fas fa-user',
                'editor': 'fas fa-edit',
                'subscriber': 'fas fa-user-plus'
            };
            return icons[role.toLowerCase()] || 'fas fa-user';
        },
        
        getRoleColorClass(role) {
            const colors = {
                'admin': 'admin-color',
                'moderator': 'moderator-color',
                'author': 'author-color',
                'member': 'member-color',
                'editor': 'author-color',
                'subscriber': 'member-color'
            };
            return colors[role.toLowerCase()] || 'member-color';
        },
        
        getRoleDescription(role) {
            const descriptions = {
                'admin': 'Full system access and control',
                'moderator': 'Content moderation and user management',
                'author': 'Content creation and editing',
                'member': 'Basic user access',
                'editor': 'Content editing and publishing',
                'subscriber': 'Read-only access'
            };
            return descriptions[role.toLowerCase()] || 'Standard user role';
        },
        
        getResourceIcon(resource) {
            const icons = {
                'dashboard': 'fas fa-tachometer-alt',
                'users': 'fas fa-users',
                'posts': 'fas fa-file-alt',
                'pages': 'fas fa-file',
                'media': 'fas fa-images',
                'comments': 'fas fa-comments',
                'settings': 'fas fa-cog',
                'plugins': 'fas fa-plug',
                'themes': 'fas fa-palette'
            };
            return icons[resource.toLowerCase()] || 'fas fa-folder';
        },
        
        getPermissionDescription(permission) {
            const descriptions = {
                'create': 'Create new items',
                'read': 'View and read items',
                'update': 'Edit existing items',
                'delete': 'Remove items',
                'manage': 'Full management access',
                'publish': 'Publish content',
                'moderate': 'Moderate content'
            };
            return descriptions[permission.toLowerCase()] || 'Permission access';
        },
        
        updatePermissions(role) {
            // Reset selected permissions
            this.selectedPermissions = {};

            // Initialize permissions arrays
            for (let resource in this.adminPermissions) {
                if (this.adminPermissions.hasOwnProperty(resource)) {
                    this.$set(this.selectedPermissions, resource, []);
                }
            }

            // Nếu đang ở chế độ edit và có user permissions từ database
            if (this.isEditMode && this.userPermissions && Object.keys(this.userPermissions).length > 0) {
                // Load permissions thực tế từ database
                for (let resource in this.userPermissions) {
                    if (this.userPermissions.hasOwnProperty(resource)) {
                        this.selectedPermissions[resource] = [...this.userPermissions[resource]];
                    }
                }
            } else if (role && this.roles[role]) {
                // Nếu không phải edit mode hoặc không có user permissions, dùng role mặc định
                const rolePermissions = this.roles[role];
                for (let resource in rolePermissions) {
                    if (rolePermissions.hasOwnProperty(resource)) {
                        this.selectedPermissions[resource] = [...rolePermissions[resource]];
                    }
                }
            }
        },
        
        isAllPermissionsInGroupEnabled(resource) {
            if (!this.selectedPermissions[resource] || !this.adminPermissions[resource]) {
                return false;
            }
            return this.adminPermissions[resource].every(permission => 
                this.selectedPermissions[resource].includes(permission)
            );
        },
        
        toggleAllPermissionsInGroup(resource, enabled) {
            if (!this.selectedPermissions[resource]) {
                this.$set(this.selectedPermissions, resource, []);
            }
            
            if (enabled) {
                this.selectedPermissions[resource] = [...this.adminPermissions[resource]];
            } else {
                this.selectedPermissions[resource] = [];
            }
        },

        resetToRoleDefaults() {
            if (this.selectedRole && this.roles[this.selectedRole]) {
                const rolePermissions = this.roles[this.selectedRole];
                
                // Reset về permissions mặc định của role
                for (let resource in this.adminPermissions) {
                    if (this.adminPermissions.hasOwnProperty(resource)) {
                        if (rolePermissions[resource]) {
                            this.selectedPermissions[resource] = [...rolePermissions[resource]];
                        } else {
                            this.selectedPermissions[resource] = [];
                        }
                    }
                }
            }
        },
        
        getGrantedPermissionsCount() {
            let count = 0;
            for (let resource in this.selectedPermissions) {
                if (this.selectedPermissions[resource]) {
                    count += this.selectedPermissions[resource].length;
                }
            }
            return count;
        },
        
        getTotalPermissionsCount() {
            let count = 0;
            for (let resource in this.adminPermissions) {
                count += this.adminPermissions[resource].length;
            }
            return count;
        },
        
        getPermissionPercentage() {
            const total = this.getTotalPermissionsCount();
            const granted = this.getGrantedPermissionsCount();
            return total > 0 ? Math.round((granted / total) * 100) : 0;
        },
        
        getPermissionLevelClass() {
            const percentage = this.getPermissionPercentage();
            if (percentage >= 90) return 'permission-level-full';
            if (percentage >= 70) return 'permission-level-high';
            if (percentage >= 40) return 'permission-level-medium';
            if (percentage >= 20) return 'permission-level-low';
            return 'permission-level-minimal';
        },
        
        getPermissionLevelText() {
            const percentage = this.getPermissionPercentage();
            if (percentage >= 90) return 'Full Access';
            if (percentage >= 70) return 'High Access';
            if (percentage >= 40) return 'Medium Access';
            if (percentage >= 20) return 'Low Access';
            return 'Minimal Access';
        }
    },
    created() {
        // Initialize permissions
        this.updatePermissions(this.selectedRole);
        
        // Set default role if none selected
        if (!this.selectedRole && Object.keys(this.roles).length > 0) {
            this.selectedRole = Object.keys(this.roles)[0];
        }
    }
});