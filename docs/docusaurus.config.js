// @ts-check
// Note: type annotations allow type checking and IDEs autocompletion

const lightCodeTheme = require('prism-react-renderer/themes/github');
const darkCodeTheme = require('prism-react-renderer/themes/dracula');

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'ToDo&Co Documentation',
  tagline: 'Bienvenue sur le site de documentation de l\'application ToDoList' ,
  url: 'https://your-docusaurus-test-site.com',
  baseUrl: '/projet8-TodoList/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',
  trailingSlash: false,

  // GitHub pages deployment config.
  // If you aren't using GitHub pages, you don't need these.
  organizationName: 'JacquesDurand', // Usually your GitHub org/user name.
  projectName: 'projet8-TodoList', // Usually your repo name.

  // Even if you don't use internalization, you can use this field to set useful
  // metadata like html lang. For example, if your site is Chinese, you may want
  // to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'fr',
    locales: ['fr'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      navbar: {
        title: 'ToDo&Co Documentation',
        logo: {
          alt: 'ToDo&Co Logo',
          src: 'img/OC.png',
        },
        items: [
          {
            type: 'doc',
            docId: 'tuto/intro',
            position: 'left',
            label: 'Documentation - Tutorials',
          },{
            type: 'doc',
            docId: 'app/intro',
            position: 'left',
            label: 'Documentation - App',
          },
          {
            href: 'https://github.com/JacquesDurand/projet8-TodoList/',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              {
                label: 'Tutorial',
                to: '/docs/tuto/intro',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} ToDo&Co, Inc. Built with Docusaurus.`,
      },
      prism: {
        theme: lightCodeTheme,
        darkTheme: darkCodeTheme,
      },
    }),
};

module.exports = config;
