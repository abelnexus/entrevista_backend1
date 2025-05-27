import { defineStore } from "pinia";
import axios from "@axios";
import axiosIns from "../../../plugins/axios";

export const useUserListStore = defineStore("UserListStore", {
  actions: {
    // 👉 Fetch users data
    fetchUsers(params) {
      return axiosIns.get("apps/users/list", { params });
    },

    // 👉 Add User
    addUser(userData) {
      return new Promise((resolve, reject) => {
        axiosIns
          .post("/apps/users/user", {
            user: userData,
          })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },

    // 👉 fetch single user
    fetchUser(id) {
      return new Promise((resolve, reject) => {
        axiosIns
          .get(`/apps/users/${id}`)
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },

    // 👉 Delete User
    deleteUser(id) {
      return new Promise((resolve, reject) => {
        axiosIns
          .delete(`users/delete/${id}`)
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
  },
});
