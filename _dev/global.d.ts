interface AutoUpgrade {
  token: string,
  admin_url: string,
  admin_dir: string,
}

declare global {
  interface Window {
    AutoUpgrade: AutoUpgrade;
  }

  const AutoUpgrade: AutoUpgrade;
}

export {};
