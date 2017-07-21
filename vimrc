"激活pathogen"
call pathogen#infect()
call pathogen#helptags()
if v:lang =~ "utf8$" || v:lang =~ "UTF-8$"
   "VIm打开文件时的尝试使用的编码"
   set fileencodings=ucs-bom,utf-8,latin1
endif

set nocompatible	" Use Vim defaults (much better!)
set bs=indent,eol,start		" allow backspacing over everything in insert mode
"set ai			" always set autoindenting on
"set backup		" keep a backup file
set viminfo='20,\"50	" read/write a .viminfo file, don't store more
			" than 50 lines of registers
set history=400		" keep 50 lines of command line history
set ruler		" show the cursor position all the time

" Only do this part when compiled with support for autocommands
if has("autocmd")
  augroup redhat
  autocmd!
  " In text files, always limit the width of text to 78 characters
  " autocmd BufRead *.txt set tw=78
  " When editing a file, always jump to the last cursor position
  autocmd BufReadPost *
  \ if line("'\"") > 0 && line ("'\"") <= line("$") |
  \   exe "normal! g'\"" |
  \ endif
  " don't write swapfile on most commonly used directories for NFS mounts or USB sticks
  autocmd BufNewFile,BufReadPre /media/*,/run/media/*,/mnt/* set directory=~/tmp,/var/tmp,/tmp
  " start with spec file template
  autocmd BufNewFile *.spec 0r /usr/share/vim/vimfiles/template.spec
  augroup END
endif

if has("cscope") && filereadable("/usr/bin/cscope")
   set csprg=/usr/bin/cscope
   set csto=0
   set cst
   set nocsverb
   " add any database in current directory
   if filereadable("cscope.out")
      cs add $PWD/cscope.out
   " else add database pointed to by environment
   elseif $CSCOPE_DB != ""
      cs add $CSCOPE_DB
   endif
   set csverb
endif

" Switch syntax highlighting on, when the terminal has colors
" Also switch on highlighting the last used search pattern.
if &t_Co > 2 || has("gui_running")
  syntax on
  set hlsearch
  "设置字体"
  set guifont=Consolas:h9
endif
"自动检测文件类型"
filetype plugin on

if &term=="xterm"
     set t_Co=8
     set t_Sb=[4%dm
     set t_Sf=[3%dm
endif

" Don't wake up system with blinking cursor:
" http://www.linuxpowertop.org/known.php
let &guicursor = &guicursor . ",a:blinkon0"
"Vim的内部编码"
set encoding=utf-8
"Vim在与屏幕键盘交互时使用的编码，取决于实际终端的设定"
set termencoding=utf-8
"Vim 当前编辑的文件在存储时的编码"
set fileencoding=utf-8
"设置中文帮助"
set helplang=cn
"设置行号"
     set nu
" 括号匹配 "
set showmatch 
"在缩进和遇到Tab建是使用空格替代"
set expandtab
"根据文件类型设置缩进格式"
au FileType html,Python,vim,JavaScript setl shiftwidth=2

au FileType html,python,vim,javascript setl tabstop=2
au FileType Java,php setl shiftwidth=4
au FileType java,php setl tabstop=4
"启动vim时不要自动折叠代码"
set foldlevel=100
"自动对齐"
set ai
"依据上面的对齐格式"
set si
set smarttab

set wrap

set lbr

set tw=0

set foldmethod=syntax
set autoread
"启用鼠标"
"se mouse=a"
"设置配色方案$vim/colors/$vim"
colorscheme freya
"Sets how may lies of history Vim har to remember"
set history=400
"Set to auto read when a file is changed from the outside"
set autoread
"Do not redraw,when running macros..lazyredraw"
set lz
"set 7 lines to the curors-when moving vertical.."
set so=7
"The commandbar is 2 high"
set cmdheight=2
"Change buffer-without saving"
set hid
"查询时忽略大小写"
set ignorecase
set incsearch
set magic
"出错时不响和闪屏"
set noerrorbells
set novisualbell
set t_vb=
"高亮显示结果"
set hlsearch
"禁止生成临时文件"
set nobackup
set nowb
set noswapfile
"使退格建正常处理indet，eol，start等如设置了set ai想删除所近必须由此选项"
set backspace=start,indent,eol
"switch buffers with Tab"
map <F4> :NERDTreeToggle<CR>
