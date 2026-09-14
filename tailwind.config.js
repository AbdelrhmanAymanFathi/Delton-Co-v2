/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./careers.html",
    "./admin/index.html",
    "./admin/index.php",
    "./assets/js/**/*.js",
    "./assets/php/**/*.php",
    "./assets/css/**/*.css"
  ],
  safelist: [
    { pattern: /(bg|text|border|ring)-(gold|navy)-(400|500|600|700|800|900|950)/ },
    { pattern: /(bg|text|border)-(gold|navy)-(400|500|600|700|800|900|950)\/\d+/ },
    { pattern: /(bg|text|border|shadow)-(white|black|gray|slate)-(50|100|200|300|400|500|600|700|800|900)/ },
    { pattern: /(p|m|px|py|pt|pr|pb|pl|mx|my|mt|mr|mb|ml|gap)-(0|1|2|3|4|5|6|8|10|12|16|20|24|auto)/ },
    { pattern: /(w|h|min-w|max-w|min-h)-(0|full|screen|auto|xs|sm|md|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|64|80|96)/ },
    { pattern: /w-(1\/2|1\/3|2\/3|1\/4|3\/4)/ },
    { pattern: /(rounded|shadow)-(sm|md|lg|xl|2xl|3xl|full|none)/ },
    { pattern: /grid-cols-(1|2|3|4|5|6)/ },
    { pattern: /(flex|grid|block|hidden|inline-block|inline-flex)/ },
    { pattern: /flex-(row|row-reverse|col|col-reverse|wrap|nowrap|1|auto|none)/ },
    { pattern: /(justify|items|self|place)-(start|end|center|between|around|evenly|stretch|baseline)/ },
    { pattern: /object-(cover|contain|fill)/ },
    { pattern: /overflow-(hidden|auto|scroll|visible)/ },
    { pattern: /(top|bottom|left|right)-(0|auto|4|8|10|12|16)/ },
    { pattern: /(fixed|absolute|relative|sticky)/ },
    { pattern: /z-(0|10|20|30|40|50)/ },
    { pattern: /font-(thin|light|normal|medium|semibold|bold|extrabold|black|cairo|poppins)/ },
    { pattern: /text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl|left|right|center|justify|nowrap)/ },
    { pattern: /(underline|line-through|no-underline)/ },
    { pattern: /(capitalize|uppercase|lowercase|normal-case)/ },
    { pattern: /tracking-(tighter|tight|normal|wide|wider|widest)/ },
    { pattern: /leading-(none|tight|snug|normal|relaxed|loose|5|6|7|8|9|10)/ },
    { pattern: /(opacity|backdrop-opacity)-(0|20|25|30|40|50|60|70|75|80|90|100)/ },
    { pattern: /(transition)-(none|all|colors|opacity|shadow|transform)/ },
    { pattern: /duration-(75|100|150|200|300|500|700|800|1000)/ },
    { pattern: /ease-(linear|in|out|in-out)/ },
    { pattern: /delay-(100|150|200|300|500|700|1000)/ },
    { pattern: /backdrop-blur-(sm|md|lg|xl)/ },
    { pattern: /scale-(90|95|100|105|110|125)/ },
    { pattern: /border-(0|2|4|8)/ },
    { pattern: /outline-(none)/ },
    { pattern: /list-(none|disc|decimal)/ },
    { pattern: /cursor-(pointer|default|not-allowed|wait|help)/ },
    { pattern: /pointer-events-(none|auto)/ },
    { pattern: /sr-only/ },
    { pattern: /animate-(pulse|bounce|spin|ping)/ },
    { pattern: /aspect-(square|video|auto)/ },
    { pattern: /inset-(0|x-0|y-0)/ },
    { pattern: /line-clamp-(1|2|3|4|5|6)/ },
    { pattern: /translate-x-(0|full)/ },
    '-translate-x-full',
    'lg:-translate-x-0',
    'rtl:mr-auto',
    'rtl:ml-0',
    'rtl:text-right',
    'rtl:space-x-reverse',
    'rtl:rotate-180',
    'ltr:rotate-0',
    'sm:ml-3',
    'md:ml-6',
    'lg:ml-8',
    'md:mr-6',
    'lg:mr-8',
    'hover:scale-105',
    'hover:scale-110',
    'hover:-translate-y-1',
    'group-hover:translate-x-1',
    'group-hover:text-gold-500',
    'focus:ring-2',
    'focus:ring-offset-2',
    'focus:ring-gold-500',
    'focus:border-gold-500',
    'md:order-first',
    'md:order-last',
    'lg:order-2',
    'sm:items-start',
    'md:items-center',
    'backdrop-blur-md',
    'backdrop-blur-xl',
    'bg-opacity-80',
    'bg-opacity-90',
    'md:bg-transparent'
  ],
  theme: {
    extend: {
      colors: {
        gold: {
          400: '#F0CB86',
          500: '#E4B15B',
          600: '#B8862E',
          700: '#8A6720'
        },
        navy: {
          800: '#18141A',
          900: '#0E0C10',
          950: '#0A080C'
        },
        yellow: {
          400: '#F0CB86',
          500: '#E4B15B'
        }
      },
      fontFamily: {
        cairo: ['Cairo', 'Arial', 'Helvetica', 'sans-serif'],
        poppins: ['Poppins', 'Arial', 'Helvetica', 'sans-serif']
      }
    }
  },
  plugins: []
};
