// assets/js/server-variable.js

// Variables to store promises for each API call
let entityDataPromise = null;
let userDataPromise = null;
let settingsDataPromise = null;

// Function to get entity data
export function getEntityData() {
  if (!entityDataPromise) {
    entityDataPromise = fetch("/docauposte/api/entity_data")
      .then((response) => response.json());
  }
  return entityDataPromise;
}

// Function to get user data
export function getUserData() {
  if (!userDataPromise) {
    userDataPromise = fetch("/docauposte/api/user_data")
      .then((response) => response.json());
  }
  return userDataPromise;
}

// Function to get settings data
export function getSettingsData() {
  if (!settingsDataPromise) {
    settingsDataPromise = fetch("/docauposte/api/settings")
      .then((response) => response.json());
  }
  return settingsDataPromise;
}