%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
% Wile E. Invoice
% LaTeX Template
% Version 1.0 (17/8/18)
%
% This template was downloaded from:
% http://www.LaTeXTemplates.com
%
% Authors:
% Peter Morrison-Whittle
% Vel (vel@LaTeXTemplates.com)
%
% License:
% CC BY-NC-SA 3.0 (http://creativecommons.org/licenses/by-nc-sa/3.0/)
%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

%----------------------------------------------------------------------------------------
%	PACKAGES AND OTHER DOCUMENT CONFIGURATIONS
%----------------------------------------------------------------------------------------

\documentclass[10pt]{article}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
% Wile E. Invoice
% Structural Definitions
% Version 1.0 (17/8/18)
%
% This template was downloaded from:
% http://www.LaTeXTemplates.com
%
% Authors:
% Peter Morrison-Whittle
% Vel (vel@LaTeXTemplates.com)
%
% License:
% CC BY-NC-SA 3.0 (http://creativecommons.org/licenses/by-nc-sa/3.0/)
%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

%----------------------------------------------------------------------------------------
%	REQUIRED PACKAGES AND MISC CONFIGURATIONS
%----------------------------------------------------------------------------------------

\usepackage{graphicx} % Required for including images

\usepackage[
a4paper, % Change to letterpaper for US Letter
top=2.5cm,
bottom=2.5cm,
left=2.5cm,
right=2.5cm
]{geometry} % Document margins

\usepackage{fp} % Required for invoice calculations

\usepackage[group-separator={,},group-minimum-digits=4, detect-all]{siunitx} % Required for automatically adding commas to large numbers, delimitting for 4 digits (e.g. 1,000) and using the current font

\usepackage{advdate} % Required for date calculation

\setlength\parindent{0pt} % Stop paragraph indentation

%----------------------------------------------------------------------------------------
%	FONTS
%----------------------------------------------------------------------------------------

\usepackage[utf8]{inputenc} % Required for inputting international characters
\usepackage[T1]{fontenc} % Output font encoding for international characters

\usepackage{tgadventor} % Use the TeX Gyre Adventor font
\renewcommand*\familydefault{\sfdefault} % Set the base font of the document to sans serif

%----------------------------------------------------------------------------------------
%	COLOURS
%----------------------------------------------------------------------------------------

\usepackage{xcolor} % Required for defining and using custom colours

\definecolor{highlightcolour}{HTML}{DF0174} % Colour used for making text stand out
\definecolor{rulecolour}{HTML}{B2BEB5} % Colour used for rules

%----------------------------------------------------------------------------------------
%	TABLES
%----------------------------------------------------------------------------------------

\usepackage{colortbl} % Required for colouring table cells (used for rules)

\usepackage{booktabs} % Required for nicer table rules

\usepackage{multirow} % Required for allowing cells to take up multiple rows in tables

\usepackage{array} % Required for customizing table spacing and features
\def\arraystretch{1.2} % Table row spacing, 1 is the default

\newcolumntype{R}[1]{>{\raggedleft\let\newline\\\arraybackslash\hspace{0pt}}m{#1}} % Define a fixed-width right-aligned column type

%----------------------------------------------------------------------------------------
%	CUSTOM COMMANDS
%----------------------------------------------------------------------------------------

\newcommand{\payeename}[1]{\renewcommand{\payeename}{#1}}
\newcommand{\payeejob}[1]{\renewcommand{\payeejob}{#1}}
\newcommand{\payeeaddresslineone}[1]{\renewcommand{\payeeaddresslineone}{#1}}
\newcommand{\payeeaddresslinetwo}[1]{\renewcommand{\payeeaddresslinetwo}{#1}}
\newcommand{\payeecontactlineone}[1]{\renewcommand{\payeecontactlineone}{#1}}
\newcommand{\payeecontactlinetwo}[1]{\renewcommand{\payeecontactlinetwo}{#1}}

\newcommand{\invoiceref}[1]{\renewcommand{\invoiceref}{#1}}
\newcommand{\invoiceissued}[1]{\renewcommand{\invoiceissued}{#1}}
\newcommand{\invoicedue}[1]{\renewcommand{\invoicedue}{#1}}
\newcommand{\projectname}[1]{\renewcommand{\projectname}{#1}}

\newcommand{\companyname}[1]{\renewcommand{\companyname}{#1}}
\newcommand{\sendername}[1]{\renewcommand{\sendername}{#1}}
\newcommand{\senderjob}[1]{\renewcommand{\senderjob}{#1}}
\newcommand{\senderaddresslineone}[1]{\renewcommand{\senderaddresslineone}{#1}}
\newcommand{\senderaddresslinetwo}[1]{\renewcommand{\senderaddresslinetwo}{#1}}
\newcommand{\sendercontactlineone}[1]{\renewcommand{\sendercontactlineone}{#1}}
\newcommand{\sendercontactlinetwo}[1]{\renewcommand{\sendercontactlinetwo}{#1}}

\newcommand{\termsandconditions}[1]{\renewcommand{\termsandconditions}{#1}}

%------------------------------------------------

% Running variables
\gdef\variableA{0}
\gdef\variableB{0}
\gdef\variableC{0}
\gdef\variableD{0}

% Cumulative variables
\gdef\TotalA{0} % Total before tax
\gdef\TotalB{0} % Tax
\gdef\TotalC{0} % Net after tax

\newcommand{\taxrate}[1]{\renewcommand{\taxrate}{#1}} % Tax rate used to automatically calculate tax

%----------------------------------------------------------------------------------------
%	INVOICE TABLES
%----------------------------------------------------------------------------------------

% Payee information (top table)
\newcommand{\invoicedtotable}{
{
\footnotesize
\begin{tabular}{p{0.25\textwidth} p{0.21\textwidth} p{0.21\textwidth} p{0.22\textwidth}}
\arrayrulecolor{rulecolour}\toprule[0.5pt] % Horizontal line at the top of the table
\multirow{3}{*}{ {\color{highlightcolour} \Huge INVOICE}} & \textbf{Recipient} & \\
& \payeename & \payeeaddresslineone & \payeecontactlineone \\
& \payeejob & \payeeaddresslinetwo & \payeecontactlinetwo \\
\arrayrulecolor{rulecolour}\bottomrule[0.5pt] % Horizontal line at the bottom of the table
\end{tabular}
}
}

%------------------------------------------------

% Invoice information table
\newcommand{\invoiceinformation}{
{
\footnotesize
\begin{tabular}{p{0.225\textwidth} p{0.225\textwidth} p{0.225\textwidth} p{0.225\textwidth}}
\textbf{Invoice Number} & \textbf{Date} & \textbf{Payment Due} & \textbf{Project Name} \\
\arrayrulecolor{rulecolour}\toprule[0.5pt] % Horizontal line
\invoiceref & \invoiceissued & \invoicedue & \projectname \\
\end{tabular}
}
}

%------------------------------------------------

% Invoice items table
\newenvironment{invoicetable}
{
\begin{tabular}{p{0.45\textwidth} R{0.15\textwidth} R{0.15\textwidth} R{0.15\textwidth}}
\textbf{ITEM} & \textbf{QUANTITY} & \textbf{RATE} & \textbf{SUBTOTAL} \\
\arrayrulecolor{rulecolour}\toprule[0.5pt]
}{
\arrayrulecolor{rulecolour}\bottomrule[0.5pt]
\\ [-1em] % Reduce whitespace before the totals
&& Gross & \$\num{\TotalA} \\
&& Tax & \$\num{\TotalB} \\ % To display the tax rate in brackets, add the following after "Tax" on this line: (\FPeval{\taxpercent}{round(\taxrate * 100, 2)}\taxpercent\%)
&& Net & \$\num{\TotalC} \\
\end{tabular}
}

%------------------------------------------------

% Terms and conditions and total amount due table
\newcommand{\amountdue}{
{
\footnotesize
\begin{tabular}{>{\arraybackslash}m{0.625\textwidth} p{0.05\textwidth} R{0.25\textwidth}}
\textbf{Terms and Conditions} & & \textbf{Amount Due} \\
\arrayrulecolor{rulecolour}\toprule[0.5pt]\\[-1.25em] % Horizontal line at the top of the table
\termsandconditions & & {\color{highlightcolour} \Huge \$\num{\TotalC}}\\
\end{tabular}
}
}

%------------------------------------------------

% Sender contact details table
\newcommand{\contactdetails}{
{
\footnotesize
\begin{tabular}{p{0.22\textwidth} p{0.22\textwidth} p{0.23\textwidth} p{0.22\textwidth}}
\arrayrulecolor{rulecolour}\toprule[0.5pt] % Horizontal line at the top of the table
\textbf{Sender} & & & \multirow{3}{0.22\textwidth}{\hfill{\color{highlightcolour}\Huge THANKS}}\\
\sendername & \senderaddresslineone & \sendercontactlineone & \\
\senderjob & \senderaddresslinetwo & \sendercontactlinetwo & \\
\arrayrulecolor{rulecolour}\bottomrule[0.5pt] % Horizontal line at the bottom of the table
\end{tabular}
}
}

%----------------------------------------------------------------------------------------
%	INVOICE ITEM LINES
%----------------------------------------------------------------------------------------

\newcommand{\invoiceitem}[3]{

% Calculation of running variables (current line)
\FPmul\gross{#2}{#3}\FPround\gross{\gross}{2} % Calculate the current gross (quantity * rate)
\global\let\variableA\gross % Set variable for use in table and other calculations

%------------------------------------------------

% Calculation of cumulative variables (total for invoice)
\FPeval{\beforetax}{round(\TotalA + \variableA, 2)} % Add the current before tax value to the previous total
\global\let\TotalA\beforetax % Set variable for display at the end and in other calculations

\FPeval{\tax}{round(\TotalA * \taxrate, 2)} % Calculate the updated total tax to pay
\global\let\TotalB\tax % Set variable for display at the end and in other calculations

\FPeval{\aftertax}{round(\TotalA + \TotalB, 2)} % Calculate the updated total amount due
\global\let\TotalC\aftertax % Set variable for display at the end

%------------------------------------------------

% Output the table row
#1 & % Item name
\num{#2} & % Quantity
\$\num{#3} & % Rate
\$\num{\variableA} \\  % Subtotal
}

%----------------------------------------------------------------------------------------
%	INVOICE ENVIRONMENT
%----------------------------------------------------------------------------------------

\newenvironment{invoice}
{
\thispagestyle{empty} % Suppress headers and footers

\begin{center}
{\color{highlightcolour}\Huge\companyname}
\end{center}

\vspace{1cm} % Whitespace before the payee information

\invoicedtotable % Payee table (whom the invoice is addressed to)
\vfill
\invoiceinformation % Invoice information table
\vfill
}{
\vfill
\amountdue % Terms and conditions and total amount due table
\vfill
\contactdetails % Contact details table
}
% Include the file specifying the document structure and styling

%----------------------------------------------------------------------------------------
%	INVOICE INFORMATION
%----------------------------------------------------------------------------------------

\taxrate{0.15} % The tax rate used to automatically calculate tax, must be set to a decimal between 0 and 1 (for no tax set to 0)

%------------------------------------------------
% Invoice information

\invoiceref{1632-F} % Invoice number
\invoiceissued{\today} % Date issued
\invoicedue{\DayAfter[7]} % Due date, a number of days into the future, use 0 for due on receipt
\projectname{Pest Eradication}

%------------------------------------------------
% Recipient/payee information

\payeename{Wile E. Coyote}
\payeejob{Ornithologist}
\payeeaddresslineone{1 Monument Valley}
\payeeaddresslinetwo{Arizona, United States}
\payeecontactlineone{wile.e@coyote.is}
\payeecontactlinetwo{LaTeXTemplates.com}

%------------------------------------------------
% Sender information

\companyname{KMT Service Privé} % Displayed in large letters at the top, can be replaced with an image or left blank for no company

\sendername{John Smith}
\senderjob{Sales Associate}
\senderaddresslineone{1 ACME Plaza}
\senderaddresslinetwo{Brooklyn, New York}
\sendercontactlineone{john@acmecorp.com}
\sendercontactlinetwo{+1 123 456 7890}

%------------------------------------------------

\termsandconditions{Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent porttitor arcu luctus, imperdiet urna iaculis, mattis eros. Pellentesque iaculis odio vel nisl ullamcorper, nec faucibus ipsum molestie. Sed dictum nisl non aliquet porttitor. Etiam vulputate arcu dignissim, finibus sem et, viverra nisl. Aenean luctus congue massa, ut laoreet metus ornare in. \newline\newline Aliquam arcu turpis, ultrices sed luctus ac, vehicula id metus. Morbi eu feugiat velit, et tempus augue. Proin ac mattis tortor. Donec tincidunt, ante rhoncus luctus semper, arcu lorem lobortis justo, nec convallis ante quam quis lectus. Aenean tincidunt sodales massa, et hendrerit tellus mattis ac. Sed non pretium nibh.} % Note: must use \newline for new lines in this command

%----------------------------------------------------------------------------------------

\begin{document}

%----------------------------------------------------------------------------------------
%	INVOICE ITEMS
%----------------------------------------------------------------------------------------

\begin{invoice}
\begin{invoicetable}
\invoiceitem{Heavy wrought anvil}{1}{5600}
\invoiceitem{Dynamite sticks}{25}{25.95}
\invoiceitem{Exploding tennis balls}{7}{49.99}
\end{invoicetable}
\end{invoice}

%----------------------------------------------------------------------------------------

\end{document}
