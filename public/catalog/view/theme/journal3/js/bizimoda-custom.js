$( document ).ready(function() {
    //$("#main-menu ul li:first").remove();
    $("#main-menu ul li:first").html('<div id="customDropDownMenu" class="menu-item main-menu-item main-menu-item-1 dropdown flyout drop-menu  first-dropdown">'+$("#main-menu ul li:first").html()+'</div>');

    // Desktop: "Kateqoriyalar" düyməsi #main-menu daxilində qalır (Journal-ın menyu üslubları itməsin),
    // header-də isə onun üçün yer (.bz-cat-slot) ayrılır və düymə dəqiq həmin yerin üstünə yerləşdirilir.
    // Əvvəl sabit koordinatlarla (left:190px, mənfi margin) idi və bəzi enlərdə axtarışın üstünə düşürdü.
    (function () {
        var $btn = $('#customDropDownMenu');
        if (!$btn.length) {
            return;
        }
        var $slot = $('<div class="bz-cat-slot" aria-hidden="true"></div>');
        $('.mid-bar .desktop-logo-wrapper').first().after($slot);
        var frame = null;

        function placeCategoriesButton() {
            frame = null;
            var desktop = document.documentElement.classList.contains('desktop-header-active');
            if (!desktop) {
                $btn.removeClass('bz-cat-placed').css({ left: '', top: '' });
                return;
            }
            var $link = $btn.children('a').first();
            $btn.addClass('bz-cat-placed');
            $slot.css('width', $link.outerWidth());
            var parent = $btn.offsetParent()[0];
            if (!parent) {
                return;
            }
            var s = $slot[0].getBoundingClientRect();
            var p = parent.getBoundingClientRect();
            $btn.css({
                left: Math.round(s.left - p.left) + 'px',
                top: Math.round(s.top - p.top + (s.height - $link.outerHeight()) / 2) + 'px'
            });
        }

        function schedule() {
            if (!frame) {
                frame = window.requestAnimationFrame(placeCategoriesButton);
            }
        }

        placeCategoriesButton();
        $(window).on('load resize scroll', schedule);
        if (window.MutationObserver) {
            // desktop/mobil header keçidi və sticky header (sinif dəyişiklikləri)
            var observer = new MutationObserver(schedule);
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            $('header').each(function () { observer.observe(this, { attributes: true, attributeFilter: ['class', 'style'] }); });
        }
    })();
    // $("#main-menu ul .dropdown-menu .flyout-menu-323 ul .flyout-menu-item-1:first").addClass('open');
    
    $('#customDropDownMenu').click(function(){
        $('#customDropDownMenu').toggleClass('customOpenMenu');
        $('#customDropDownMenu').parent().toggleClass('customOpenMenuParent');
         setTimeout( function(){ 
               $('#customDropDownMenu .dropdown-menu .flyout-menu-323 .flyout-menu-item:first').addClass('animating');
               $('#customDropDownMenu .dropdown-menu .flyout-menu-323 .flyout-menu-item:first').addClass('open');
          }  , 300);
    });

   $( "#customDropDownMenu .dropdown-menu .flyout-menu-323 .flyout-menu-item" ).hover(function() {
       $('#customDropDownMenu .dropdown-menu .flyout-menu-323 .flyout-menu-item').removeClass('open');
       $('#customDropDownMenu .dropdown-menu .flyout-menu-323 .flyout-menu-item').removeClass('animating');
       $(this).addClass('open');
       $(this).addClass('animating');
   });

});
$(document).ready(function() {
  // First, close all categories
  $('.module-item.module-item-collapse .module-item-content').hide();
  
  // Get current category from URL or another identifier on the page
  var currentUrl = window.location.href;
  
  // Check each category and expand the relevant one
  $('.module-item.module-item-collapse').each(function() {
    var categoryLink = $(this).find('.module-item-header a').attr('href');
    if (categoryLink && currentUrl.indexOf(categoryLink) > -1) {
      $(this).find('.module-item-content').show();
    }
  });
});
// This script automatically expands the correct category and highlights the active category
document.addEventListener('DOMContentLoaded', function() {
  // First, close all accordion panels (collapse them)
  var allPanels = document.querySelectorAll('.panel-collapse.collapse.in');
  allPanels.forEach(function(panel) {
    panel.classList.remove('in');
    var panelHeading = panel.previousElementSibling;
    if (panelHeading) {
      var accordionToggle = panelHeading.querySelector('.accordion-toggle');
      if (accordionToggle) {
        accordionToggle.classList.add('collapsed');
        accordionToggle.setAttribute('aria-expanded', 'false');
      }
    }
  });

  // Remove any existing active-category class (for refreshes)
  var previousActiveItems = document.querySelectorAll('.active-category');
  previousActiveItems.forEach(function(item) {
    item.classList.remove('active-category');
  });

  // Get the current path from the URL
  var currentPath = window.location.pathname;
  var currentUrl = window.location.href;
  
  // Look for all category links
  var categoryLinks = document.querySelectorAll('.module-item a');
  
  var foundMatch = false;
  
  // Check each link to see if it matches the current page
  categoryLinks.forEach(function(link) {
    if (currentPath === link.getAttribute('href') || 
        currentUrl.includes(link.getAttribute('href'))) {
      
      foundMatch = true;
      
      // Add highlighting class to the link (or its parent li/div)
      var linkParent = link.parentElement;
      linkParent.classList.add('active-category');
      
      // Find the parent accordion panel
      var panel = findParentPanel(link);
      if (panel) {
        // Open the panel
        panel.classList.add('in');
        panel.style.height = 'auto';
        
        // Update the toggle button state
        var panelHeading = panel.previousElementSibling;
        if (panelHeading) {
          var accordionToggle = panelHeading.querySelector('.accordion-toggle');
          if (accordionToggle) {
            accordionToggle.classList.remove('collapsed');
            accordionToggle.setAttribute('aria-expanded', 'true');
          }
        }
      }
    }
  });
  
  // If no match was found, open the first category (default behavior)
  if (!foundMatch) {
    var firstPanel = document.querySelector('.panel-collapse.collapse');
    if (firstPanel) {
      firstPanel.classList.add('in');
      var panelHeading = firstPanel.previousElementSibling;
      if (panelHeading) {
        var accordionToggle = panelHeading.querySelector('.accordion-toggle');
        if (accordionToggle) {
          accordionToggle.classList.remove('collapsed');
          accordionToggle.setAttribute('aria-expanded', 'true');
        }
      }
    }
  }
  
  // Helper function to find the parent panel of an element
  function findParentPanel(element) {
    var current = element;
    while (current) {
      // Go up the DOM tree
      current = current.parentElement;
      
      // Check if we've found the panel
      if (current && current.classList.contains('panel-collapse')) {
        return current;
      }
      
      // If we've reached the top-level category module, stop searching
      if (current && current.classList.contains('module-categories')) {
        return null;
      }
    }
    return null;
  }
});

// Add custom CSS for highlighting the active category
var style = document.createElement('style');
style.textContent = `
  .active-category > a {
    background-color: #f2f2f2 !important;
    font-weight: bold !important;
    color: #e42f35 !important;
    border-left: 3px solid #e42f35 !important;
    padding-left: 12px !important;
  }
`;
document.head.appendChild(style);


//edcode
/**
 * OpenCart Journal Theme - Sticky Header Script
 * Makes the header sticky on scroll for better navigation experience
 */

(function($) {
    'use strict';
    
    // Configuration
    const config = {
        // Multiple selectors to match different header structures
        headerSelector: '.header, .header-classic, .header-lg, header, .desktop-header, .main-header',
        stickyClass: 'header-sticky',
        offset: 50, // Scroll offset before header becomes sticky (reduced for better UX)
        animationDuration: 250,
        zIndex: 9999,
        // Additional options for bizimoda.az
        mobileBreakpoint: 768,
        enableMobile: false, // Disable on mobile for better performance
        hideOnScroll: false, // Set to true if you want header to hide when scrolling down
        backgroundColor: '#ffffff' // Background color for sticky header
    };
    
    // Main sticky header functionality
    function initStickyHeader() {
        // Yalnız bir dəfə: əvvəllər hər AJAX-dan sonra yenidən bağlanırdı (təkrar scroll handler-ləri)
        if (window.__bzStickyInit) {
            return;
        }
        window.__bzStickyInit = true;

        // Try to find the header element using multiple selectors
        let $header = $(config.headerSelector).first();
        
        // If not found, try common header patterns for OpenCart/Journal
        if (!$header.length) {
            const fallbackSelectors = [
                'header.header',
                '.header-wrapper',
                '.header-container',
                '#header',
                '.top-header',
                '.site-header'
            ];
            
            for (let selector of fallbackSelectors) {
                $header = $(selector).first();
                if ($header.length) break;
            }
        }
        
        const $window = $(window);
        const $body = $('body');
        
        if (!$header.length) {
            console.warn('Bizimoda sticky header: Header element not found with any selector');
            return;
        }
        
        console.log('Bizimoda sticky header: Found header element', $header[0]);
        
        // Store original header properties
        const headerData = {
            originalPosition: $header.css('position'),
            originalTop: $header.css('top'),
            originalZIndex: $header.css('z-index'),
            height: $header.outerHeight()
        };
        
        // Add CSS for sticky behavior
        addStickyStyles();
        
        // Handle scroll events with direction detection
        let isSticky = false;
        let ticking = false;
        let lastScrollTop = 0;
        let scrollDirection = 'down';
        
        function handleScroll() {
            const scrollTop = $window.scrollTop();
            
            // Detect scroll direction
            if (scrollTop > lastScrollTop) {
                scrollDirection = 'down';
            } else {
                scrollDirection = 'up';
            }
            lastScrollTop = scrollTop;
            
            // Check if we should make header sticky
            if (scrollTop > config.offset && !isSticky) {
                // Only make sticky if not on mobile (unless enabled)
                if (config.enableMobile || $window.width() > config.mobileBreakpoint) {
                    makeSticky();
                }
            } else if (scrollTop <= config.offset && isSticky) {
                removeSticky();
            }
            
            // Handle hide on scroll functionality
            if (config.hideOnScroll && isSticky) {
                if (scrollDirection === 'down' && scrollTop > config.offset + 100) {
                    $header.addClass('header-hidden');
                } else if (scrollDirection === 'up') {
                    $header.removeClass('header-hidden');
                }
            }
            
            ticking = false;
        }
        
        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(handleScroll);
                ticking = true;
            }
        }
        
        function makeSticky() {
            isSticky = true;
            
            // Add placeholder to prevent layout jump
            const $placeholder = $('<div>')
                .addClass('header-sticky-placeholder')
                .css('height', headerData.height + 'px');
            
            $header.before($placeholder);
            
            // Apply sticky styles
            $header.addClass(config.stickyClass);
            $body.addClass('has-sticky-header');
            
            // Animate in
            $header.css({
                'transform': 'translateY(-100%)',
                'transition': 'none'
            });
            
            setTimeout(() => {
                $header.css({
                    'transform': 'translateY(0)',
                    'transition': `transform ${config.animationDuration}ms ease-in-out`
                });
            }, 10);
        }
        
        function removeSticky() {
            isSticky = false;
            
            // Animate out
            $header.css('transform', 'translateY(-100%)');
            
            setTimeout(() => {
                $header.removeClass(config.stickyClass);
                $body.removeClass('has-sticky-header');
                $('.header-sticky-placeholder').remove();
                
                // Reset styles
                $header.css({
                    'transform': '',
                    'transition': ''
                });
            }, config.animationDuration);
        }
        
        // Bind scroll event with throttling
        $window.on('scroll', requestTick);
        
        // Handle window resize
        $window.on('resize', debounce(function() {
            if (isSticky) {
                headerData.height = $header.outerHeight();
                $('.header-sticky-placeholder').css('height', headerData.height + 'px');
            }
        }, 250));
        
        // Initialize on page load
        handleScroll();
    }
    
    // Add necessary CSS styles
    function addStickyStyles() {
        const styles = `
            <style id="bizimoda-sticky-header-styles">
                .header-sticky {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    width: 100% !important;
                    z-index: ${config.zIndex} !important;
                    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
                    background: ${config.backgroundColor} !important;
                    border-bottom: 1px solid #e1e1e1;
                    transform: translateY(0);
                    transition: transform ${config.animationDuration}ms ease-in-out;
                }
                
                .header-sticky.header-hidden {
                    transform: translateY(-100%);
                }
                
                .header-sticky-placeholder {
                    display: block;
                    width: 100%;
                }
                
                .has-sticky-header .header-sticky {
                    animation: slideDown ${config.animationDuration}ms ease-in-out;
                }
                
                @keyframes slideDown {
                    from {
                        transform: translateY(-100%);
                    }
                    to {
                        transform: translateY(0);
                    }
                }
                
                /* Bizimoda.az specific adjustments */
                .header-sticky .logo img {
                    max-height: 40px !important;
                    transition: all 0.3s ease;
                }
                
                .header-sticky .search-wrapper {
                    margin: 5px 0;
                }
                
                .header-sticky .nav-item {
                    padding: 10px 8px;
                }
                
                /* Mobile responsive adjustments */
                @media (max-width: ${config.mobileBreakpoint}px) {
                    .header-sticky {
                        position: relative !important;
                        box-shadow: none !important;
                        border-bottom: none !important;
                    }
                    
                    .header-sticky-placeholder {
                        display: none !important;
                    }
                    
                    .header-sticky .logo img {
                        max-height: inherit !important;
                    }
                }
                
                /* Ensure dropdown menus and mega menus appear above other content */
                .header-sticky .dropdown-menu,
                .header-sticky .mega-menu,
                .header-sticky .submenu {
                    z-index: ${config.zIndex + 10} !important;
                }
                
                /* Smooth transitions for header elements */
                .header-sticky .nav-link,
                .header-sticky .btn,
                .header-sticky .search-input {
                    transition: all 0.2s ease;
                }
                
                /* Language and currency switchers */
                .header-sticky .language,
                .header-sticky .currency {
                    font-size: 13px;
                }
                
                /* Cart and account icons */
                .header-sticky .header-icons {
                    margin: 5px 0;
                }
                
                /* Compact sticky header styles */
                .header-sticky {
                    min-height: 60px;
                    padding: 5px 0;
                }
            </style>
        `;
        
        // Remove existing styles and add new ones
        $('#bizimoda-sticky-header-styles').remove();
        $('head').append(styles);
    }
    
    // Utility function for debouncing
    function debounce(func, wait, immediate) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    
    // Public API for customization
    window.BizimodaStickyHeader = {
        init: initStickyHeader,
        config: config,
        
        // Method to update configuration
        configure: function(options) {
            $.extend(config, options);
            return this;
        },
        
        // Method to destroy sticky header
        destroy: function() {
            $(window).off('scroll resize');
            $('.header-sticky').removeClass('header-sticky header-hidden');
            $('.header-sticky-placeholder').remove();
            $('body').removeClass('has-sticky-header');
            $('#bizimoda-sticky-header-styles').remove();
        },
        
        // Method to manually trigger sticky state
        makeSticky: function() {
            const $header = $(config.headerSelector).first();
            if ($header.length && !$header.hasClass(config.stickyClass)) {
                $header.addClass(config.stickyClass);
                $('body').addClass('has-sticky-header');
            }
        },
        
        // Method to remove sticky state
        removeSticky: function() {
            const $header = $(config.headerSelector).first();
            if ($header.length) {
                $header.removeClass(config.stickyClass + ' header-hidden');
                $('body').removeClass('has-sticky-header');
                $('.header-sticky-placeholder').remove();
            }
        }
    };
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        // Small delay to ensure all Journal theme scripts are loaded
        setTimeout(initStickyHeader, 100);
    });
    
    // Re-initialize on AJAX page loads and other dynamic content
    $(document).on('ajaxComplete', function() {
        setTimeout(initStickyHeader, 150);
    });
    
    // Handle OpenCart common events
    $(document).on('journal_ajax_loaded opencart_loaded content_loaded', function() {
        setTimeout(initStickyHeader, 150);
    });
    
})(jQuery);

// Cookie bildirişi: "Qəbul edirəm" Journal-ın öz bağlama düyməsini işə salır (n-<hash> cookie-si orada yazılır).
$(document).on('click', '[data-bz-cookie-accept]', function () {
    $(this).closest('.bz-cookie').find('.notification-close').trigger('click');
});
