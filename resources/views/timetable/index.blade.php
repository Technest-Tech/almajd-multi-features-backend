<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Timetable Calendar</title>
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Cairo', 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 10px;
        }

        .topbar {
            background: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        .topbar-title {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-title i {
            color: #667eea;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media screen and (max-width: 768px) {
            .topbar {
                padding: 12px 15px;
                margin-bottom: 15px;
            }

            .topbar-title {
                font-size: 18px;
            }

            .topbar-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }

        .main-wrapper {
            display: flex;
            gap: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .sidebar {
            width: 250px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .sidebar-btn {
            width: 100%;
            padding: 15px 20px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-weight: 600;
            color: #495057;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            font-size: 14px;
        }

        .sidebar-btn i {
            font-size: 18px;
            width: 24px;
            flex-shrink: 0;
        }

        .sidebar-btn:hover {
            background: #e9ecef;
            border-color: #667eea;
            transform: translateX(5px);
        }

        .sidebar-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .sidebar-btn-create {
            margin-top: 20px !important;
        }

        /* Floating toggle button for mobile */
        .sidebar-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            align-items: center;
            justify-content: center;
        }

        .sidebar-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .sidebar-toggle.active {
            transform: rotate(180deg);
        }

        .sidebar-toggle.hide {
            opacity: 0;
            pointer-events: none;
            transform: scale(0);
        }

        /* Backdrop overlay when sidebar is open */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar-backdrop.show {
            display: block;
            opacity: 1;
        }

        @media screen and (max-width: 768px) {
            .sidebar-btn-create {
                margin-top: 0 !important;
            }

            .sidebar-toggle {
                display: flex;
            }

            .sidebar {
                transform: translateY(100%);
                transition: transform 0.3s ease-in-out;
            }

            .sidebar.show {
                transform: translateY(0);
            }
        }

        .content-area {
            flex: 1;
            min-width: 0;
        }

        .calendar-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 15px;
        }

        .view-section {
            display: none;
        }

        .view-section.active {
            display: block;
        }

        .calendar-header {
            display: none; /* Hidden since we have topbar now */
        }

        .calendar-title {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .calendar-title i {
            color: #667eea;
        }

        .btn-create-timetable {
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            white-space: nowrap;
            font-size: 13px;
        }

        .btn-create-timetable:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-create-timetable-inline {
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            white-space: nowrap;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-create-timetable-inline:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .filters-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .filters-section.collapsed {
            max-height: 60px;
            padding: 10px 15px;
        }

        .filters-section.collapsed .filters-content {
            display: none !important;
        }

        .filters-content {
            margin-top: 15px;
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }

        .filters-title {
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .filters-toggle {
            background: none;
            border: none;
            color: #667eea;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
            transition: transform 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }

        .filters-toggle:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .filters-toggle.rotated {
            transform: rotate(180deg);
        }

        .filters-content {
            margin-top: 15px;
        }

        .filters-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: block;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .quick-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .quick-filter-btn {
            padding: 6px 12px;
            font-size: 12px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .quick-filter-btn:hover {
            background: #667eea;
            color: white;
        }

        .quick-filter-btn.active {
            background: #667eea;
            color: white;
        }

        .apply-filters-btn {
            padding: 8px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .apply-filters-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        /* FullCalendar Custom Styling */
        #calendar {
            background: white;
            border-radius: 10px;
            padding: 10px;
        }

        .fc {
            font-family: 'Inter', sans-serif;
        }

        .fc-header-toolbar {
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .fc-toolbar-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
        }

        .fc-button {
            background: #667eea !important;
            border: none !important;
            padding: 8px 16px !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            transition: all 0.3s !important;
        }

        .fc-button:hover {
            background: #764ba2 !important;
            transform: translateY(-2px);
        }

        .fc-button:disabled {
            opacity: 0.5;
        }

        .fc-button-primary:not(:disabled):active,
        .fc-button-primary:not(:disabled).fc-button-active {
            background: #764ba2 !important;
        }

        .fc-event {
            border-radius: 6px !important;
            padding: 4px 6px !important;
            font-size: 12px !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            border: none !important;
            min-height: 24px !important;
        }

        .fc-event:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 10 !important;
        }

        .fc-event-title {
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fc-daygrid-event {
            margin: 2px 0 !important;
        }

        .fc-timegrid-event {
            border-radius: 6px !important;
        }

        /* Event Details Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background-color: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        .modal-large {
            max-width: 800px;
        }

        .modal-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            gap: 15px;
        }

        .modal-loading p {
            color: #6c757d;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .form-label .required {
            color: #e74c3c;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .days-of-week-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .day-checkbox {
            display: none;
        }

        .day-label {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: #495057;
            user-select: none;
            font-size: 13px;
        }

        .day-checkbox:checked + .day-label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        .day-label:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .row {
            display: flex;
            gap: 15px;
        }

        .col {
            flex: 1;
        }

        .modal-footer-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .btn-cancel {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .btn-submit-modal {
            padding: 10px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit-modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-submit-modal:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-submit-modal .spinner {
            width: 16px;
            height: 16px;
            border-width: 2px;
        }

        /* Settings Styles */
        .settings-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 20px;
        }

        .settings-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .settings-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e9ecef;
        }

        .settings-section-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .settings-section-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-section-header h2 i {
            color: #667eea;
        }

        .settings-description {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }

        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .hour-adjustment-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-hour-adjust {
            width: 40px;
            height: 40px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s;
        }

        .btn-hour-adjust:hover {
            background: #667eea;
            color: white;
            transform: scale(1.1);
        }

        .hour-input {
            flex: 1;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }

        .affected-count {
            padding: 12px;
            background: #e7f3ff;
            border-left: 4px solid #3498db;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #2980b9;
            font-weight: 500;
        }

        .affected-count i {
            font-size: 18px;
        }

        .btn-apply-timezone,
        .btn-send-whatsapp {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
        }

        .btn-apply-timezone:hover,
        .btn-send-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-apply-timezone:disabled,
        .btn-send-whatsapp:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .date-selection {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 500;
            color: #495057;
        }

        .radio-label input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        @media screen and (max-width: 768px) {
            .settings-container {
                padding: 15px;
            }

            .settings-section {
                padding: 20px;
            }

            .settings-section-header h2 {
                font-size: 18px;
            }

            .hour-adjustment-controls {
                gap: 8px;
            }

            .btn-hour-adjust {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .date-selection {
                flex-direction: column;
                gap: 10px;
            }

            .row {
                flex-direction: column;
            }
        }

        .btn-confirm {
            padding: 10px 24px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-confirm.warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }

        .btn-confirm.success {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }

        .search-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input-wrapper i {
            position: absolute;
            left: 15px;
            color: #6c757d;
            z-index: 1;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-clear {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 5px;
            display: none;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .search-clear:hover {
            background: #e9ecef;
            color: #495057;
        }

        .search-clear.show {
            display: flex;
        }

        .recurrence-btn {
            padding: 10px 16px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }

        .recurrence-btn:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .recurrence-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        .recurrence-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
        }

        .event-detail-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .event-detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .event-detail-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .event-detail-value {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-scheduled {
            background: #3498db;
            color: white;
        }

        .status-completed {
            background: #2ecc71;
            color: white;
        }

        .status-cancelled {
            background: #e74c3c;
            color: white;
        }

        .loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.show {
            display: flex;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .timetables-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 20px;
        }

        .timetable-card {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .timetable-card.inactive {
            opacity: 0.7;
            background: #e9ecef;
        }

        .timetable-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .timetable-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            min-width: 100px;
            justify-content: center;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-edit:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-deactivate {
            background: #f39c12;
            color: white;
        }

        .btn-deactivate:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .btn-action:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .timetable-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .timetable-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .timetable-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #667eea;
            color: white;
        }

        .timetable-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item i {
            color: #667eea;
            width: 20px;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
        }

        .days-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: #e9ecef;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            color: #495057;
            margin: 2px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 16px;
        }

        /* Mobile Responsive Styles */
        @media screen and (max-width: 768px) {
            body {
                padding: 5px;
                padding-bottom: 20px; /* Reduced since sidebar is hidden by default */
            }

            .main-wrapper {
                flex-direction: column;
                gap: 10px;
            }

            .sidebar {
                width: 100%;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                border-radius: 20px 20px 0 0;
                box-shadow: 0 -5px 20px rgba(0,0,0,0.2);
                padding: 10px 15px 15px 15px;
                display: flex;
                flex-direction: row;
                gap: 8px;
                max-width: 100%;
                margin: 0;
                top: auto;
                transform: translateY(100%);
                transition: transform 0.3s ease-in-out;
                pointer-events: none; /* Prevent touch events when closed */
            }

            .sidebar.show {
                transform: translateY(0);
                pointer-events: auto; /* Enable touch events when open */
            }

            .sidebar-title {
                display: none;
            }

            .sidebar-btn {
                flex: 1;
                margin-bottom: 0;
                padding: 12px 8px;
                font-size: 11px;
                justify-content: center;
                flex-direction: column;
                gap: 5px;
                min-height: 70px;
                border-radius: 12px;
            }

            .sidebar-btn i {
                font-size: 20px;
                width: auto;
            }

            .sidebar-btn span {
                font-size: 10px;
                line-height: 1.2;
            }

            .sidebar-btn:hover {
                transform: translateY(-2px);
            }

            .content-area {
                width: 100%;
                margin-bottom: 0; /* No margin needed since sidebar is hidden */
            }

            .calendar-container {
                padding: 10px;
                border-radius: 10px;
            }

            .calendar-header {
                flex-direction: column;
                align-items: stretch;
            }

            .calendar-title {
                font-size: 20px;
                justify-content: center;
            }

            .btn-create-timetable {
                width: 100%;
                justify-content: center;
                padding: 10px 16px;
                font-size: 12px;
            }

            .btn-create-timetable-inline {
                width: 100%;
                justify-content: center;
                padding: 10px 16px;
                font-size: 12px;
                margin-top: 10px;
            }

            .timetable-actions {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
            }

            .search-section {
                padding: 12px;
            }

            .search-input {
                padding: 10px 12px 10px 40px;
                font-size: 14px;
            }

            .recurrence-btn {
                min-width: 100%;
                margin-bottom: 8px;
            }

            .filters-section {
                padding: 12px;
            }

            .filters-section.collapsed {
                max-height: 50px;
                padding: 8px 12px;
            }

            .filters-title {
                font-size: 14px;
            }

            .filters-row {
                flex-direction: column;
            }

            .filter-group {
                min-width: 100%;
            }

            .quick-filters {
                justify-content: center;
            }

            .apply-filters-btn {
                width: 100%;
            }

            .fc-header-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .fc-toolbar-title {
                font-size: 18px;
                text-align: center;
                margin-bottom: 10px;
            }

            .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 5px;
            }

            .fc-button {
                padding: 6px 10px !important;
                font-size: 11px !important;
                min-width: auto !important;
            }

            .fc-button-group {
                display: flex;
                flex-wrap: wrap;
                gap: 3px;
            }

            .fc-event {
                font-size: 11px !important;
                padding: 3px 5px !important;
                min-height: 20px !important;
            }

            .modal-content {
                margin: 10px;
                max-width: calc(100% - 20px);
                border-radius: 10px;
            }

            .modal-header {
                padding: 15px;
            }

            .modal-header h3 {
                font-size: 18px;
            }

            .modal-body {
                padding: 20px;
            }

            .event-detail-value {
                font-size: 14px;
            }
        }

        @media screen and (max-width: 480px) {
            body {
                padding-bottom: 85px;
            }

            .sidebar {
                padding: 8px 10px 12px 10px;
                gap: 6px;
            }

            .sidebar-btn {
                padding: 10px 6px;
                font-size: 10px;
                min-height: 65px;
                gap: 4px;
            }

            .sidebar-btn i {
                font-size: 18px;
            }

            .sidebar-btn span {
                font-size: 9px;
            }

            .content-area {
                margin-bottom: 0;
            }

            .calendar-title {
                font-size: 18px;
            }

            .fc-toolbar-title {
                font-size: 16px;
            }

            .fc-button {
                padding: 5px 10px !important;
                font-size: 11px !important;
            }

            .fc-event-title {
                font-size: 10px !important;
            }

            .modal-header {
                padding: 12px;
            }

            .modal-body {
                padding: 15px;
            }
        }

        /* List view optimizations for mobile */
        @media screen and (max-width: 768px) {
            .fc-list-event {
                padding: 10px !important;
            }

            .fc-list-event-title {
                font-size: 14px !important;
            }
        }

        /* Touch-friendly interactions */
        @media (hover: none) and (pointer: coarse) {
            .fc-event {
                min-height: 44px !important;
            }

            .fc-button {
                min-height: 44px !important;
                min-width: 44px !important;
            }

            .sidebar-btn {
                min-height: 70px !important;
                min-width: 60px !important;
            }

            /* Ensure sidebar is always accessible on mobile */
            .sidebar {
                position: fixed !important;
                bottom: 0 !important;
            }
        }

        /* Landscape mobile orientation */
        @media screen and (max-width: 768px) and (orientation: landscape) {
            .sidebar {
                flex-direction: row;
                padding: 8px 15px;
            }

            .sidebar-btn {
                min-height: 60px;
                padding: 8px 6px;
            }
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">
            <i class="fas fa-calendar-alt"></i>
            <span>Timetable Calendar</span>
        </div>
        <div class="topbar-actions">
            <button type="button" class="btn btn-primary" onclick="refreshCalendar()" title="Refresh Calendar">
                <i class="fas fa-sync-alt"></i>
                <span class="d-none d-md-inline">Refresh</span>
            </button>
        </div>
    </div>

    <!-- Backdrop overlay -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Floating toggle button for mobile -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation">
        <i class="fas fa-bars" id="toggleIcon"></i>
    </button>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-title">
                <i class="fas fa-bars"></i> Menu
            </div>
            <button class="sidebar-btn active" data-view="calendar">
                <i class="fas fa-calendar-alt"></i>
                <span>Calendar</span>
            </button>
            <button class="sidebar-btn" data-view="timetables">
                <i class="fas fa-list"></i>
                <span>All Timetables</span>
            </button>
            <button class="sidebar-btn" data-view="settings">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </button>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Calendar View -->
            <div class="view-section active" id="calendarView">
                <div class="calendar-container">
                    <div class="loading-overlay" id="loadingOverlay">
                        <div class="spinner"></div>
                    </div>

                    <div class="calendar-header">
                        <h1 class="calendar-title">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Timetable Calendar</span>
                        </h1>
                    </div>

                    <div class="filters-section collapsed" id="filtersSection">
                        <div class="filters-header" onclick="toggleFilters()">
                            <div class="filters-title">
                                <i class="fas fa-filter"></i>
                                <span>Filters</span>
                            </div>
                            <button type="button" class="filters-toggle" id="filtersToggle" aria-label="Toggle filters">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="filters-content">
            <form id="filterForm">
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="studentFilter">Student</label>
                        <select id="studentFilter" name="student_id" class="form-select" onchange="applyCalendarFilters()">
                            <option value="">All Students</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ isset($filters['student_id']) && $filters['student_id'] == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="teacherFilter">Teacher</label>
                        <select id="teacherFilter" name="teacher_id" class="form-select" onchange="applyCalendarFilters()">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ isset($filters['teacher_id']) && $filters['teacher_id'] == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="fromDate">From Date</label>
                        <input type="date" id="fromDate" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                    </div>

                    <div class="filter-group">
                        <label for="toDate">To Date</label>
                        <input type="date" id="toDate" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="apply-filters-btn">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>

                <div class="quick-filters">
                    <button type="button" class="quick-filter-btn" data-filter="today">Today</button>
                    <button type="button" class="quick-filter-btn" data-filter="week">This Week</button>
                    <button type="button" class="quick-filter-btn" data-filter="month">This Month</button>
                    <button type="button" class="quick-filter-btn" data-filter="clear">Clear Filters</button>
                    </div>
                    </form>
                        </div>
                </div>

                <div id="calendar"></div>
                </div>
            </div>

            <!-- Timetables List View -->
            <div class="view-section" id="timetablesView">
                <div class="timetables-list">
                    <div class="calendar-header">
                        <h1 class="calendar-title">
                            <i class="fas fa-list"></i>
                            <span>All Timetables</span>
                        </h1>
                        <button type="button" class="btn-create-timetable-inline" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i> Create Timetable
                        </button>
                    </div>

                    <div class="search-section">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   id="timetableSearch" 
                                   class="search-input" 
                                   placeholder="Search by student name, teacher name, or course name..."
                                   oninput="filterTimetables()">
                            <button type="button" class="search-clear" id="searchClear" onclick="clearSearch()" title="Clear search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="timetable-filters-row" style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
                            <div class="filter-group" style="flex: 1; min-width: 150px;">
                                <label for="timetableStudentFilter" style="font-size: 12px; color: #6c757d; margin-bottom: 5px; display: block;">Filter by Student</label>
                                <select id="timetableStudentFilter" class="form-select" style="font-size: 14px;" onchange="filterTimetables()">
                                    <option value="">All Students</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 1; min-width: 150px;">
                                <label for="timetableTeacherFilter" style="font-size: 12px; color: #6c757d; margin-bottom: 5px; display: block;">Filter by Teacher</label>
                                <select id="timetableTeacherFilter" class="form-select" style="font-size: 14px;" onchange="filterTimetables()">
                                    <option value="">All Teachers</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="timetables-list-content" id="timetablesListContent">
                    @if($timetables->count() > 0)
                        @foreach($timetables as $timetable)
                            <div class="timetable-card {{ ($timetable->is_active ?? 1) ? '' : 'inactive' }}" 
                                 data-student-id="{{ $timetable->student_id ?? '' }}" 
                                 data-teacher-id="{{ $timetable->teacher_id ?? '' }}">
                                <div class="timetable-header">
                                    <h3 class="timetable-title">{{ $timetable->course_name }}</h3>
                                    <span class="timetable-badge">{{ $timetable->timezone }}</span>
                                </div>
                                
                                <div class="timetable-info">
                                    <div class="info-item">
                                        <i class="fas fa-user-graduate"></i>
                                        <div>
                                            <div class="info-label">Student</div>
                                            <div class="info-value">{{ $timetable->student->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <div>
                                            <div class="info-label">Teacher</div>
                                            <div class="info-value">{{ $timetable->teacher->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <div>
                                            <div class="info-label">Time</div>
                                            <div class="info-value">
                                                {{ \Carbon\Carbon::parse($timetable->start_time)->format('g:i A') }} - 
                                                {{ \Carbon\Carbon::parse($timetable->end_time)->format('g:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <div class="info-label">Date Range</div>
                                            <div class="info-value">
                                                {{ $timetable->start_date->format('M d, Y') }} - 
                                                {{ $timetable->end_date->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-calendar-week"></i>
                                        <div>
                                            <div class="info-label">Days</div>
                                            <div class="info-value">
                                                @php
                                                    $dayNames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                                                    $days = $timetable->days_of_week ?? [];
                                                @endphp
                                                @foreach($days as $day)
                                                    <span class="days-badge">{{ $dayNames[$day] ?? $day }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-calendar-check"></i>
                                        <div>
                                            <div class="info-label">Events</div>
                                            <div class="info-value">{{ $timetable->events->count() }} scheduled</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="timetable-actions">
                                    <button type="button" class="btn-action btn-edit" onclick="openEditModal({{ $timetable->id }})" title="Edit Timetable">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn-action btn-deactivate" onclick="toggleTimetableStatus({{ $timetable->id }}, {{ $timetable->is_active ?? 1 }})" title="{{ ($timetable->is_active ?? 1) ? 'Deactivate' : 'Activate' }} Timetable">
                                        <i class="fas fa-{{ ($timetable->is_active ?? 1) ? 'pause' : 'play' }}"></i> {{ ($timetable->is_active ?? 1) ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button type="button" class="btn-action btn-delete" onclick="deleteTimetable({{ $timetable->id }})" title="Delete Timetable">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                            @endforeach

                            @if($timetables->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $timetables->links() }}
                                </div>
                            @endif
                        @else
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h3>No Timetables Found</h3>
                                <p>No timetables available at the moment.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Settings View -->
            <div class="view-section" id="settingsView">
                <div class="settings-container">
                    <div class="calendar-header">
                        <h1 class="calendar-title">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </h1>
                    </div>

                    <div class="settings-content">
                        <!-- Section 1: Timezone Adjustment -->
                        <div class="settings-section">
                            <div class="settings-section-header">
                                <h2><i class="fas fa-globe"></i> Timezone Adjustment</h2>
                                <p class="settings-description">Adjust time for all timetables in a specific country</p>
                            </div>
                            
                            <div class="settings-form">
                                <div class="form-group">
                                    <label for="timezoneCountry">Country *</label>
                                    <select id="timezoneCountry" class="form-control" required>
                                        <option value="">Select a country</option>
                                        <option value="Canada">Canada</option>
                                        <option value="America">America</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="France">France</option>
                                        <option value="Australia">Australia</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Hour Adjustment *</label>
                                    <div class="hour-adjustment-controls">
                                        <button type="button" class="btn-hour-adjust" onclick="adjustHours(-1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" id="hourAdjustment" class="form-control hour-input" value="0" min="-23" max="23" step="1" required>
                                        <button type="button" class="btn-hour-adjust" onclick="adjustHours(1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Positive values add hours, negative values subtract hours</small>
                                </div>

                                <div class="form-group">
                                    <div id="timezoneAffectedCount" class="affected-count" style="display: none;">
                                        <i class="fas fa-info-circle"></i>
                                        <span id="affectedCountText">0 timetables will be affected</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn-apply-timezone" onclick="applyTimezoneAdjustment()">
                                        <i class="fas fa-check"></i> Apply Adjustment
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: WhatsApp Reminders -->
                        <div class="settings-section">
                            <div class="settings-section-header">
                                <h2><i class="fab fa-whatsapp"></i> Send WhatsApp Reminder</h2>
                                <p class="settings-description">Send formatted timetable reminders via WhatsApp</p>
                            </div>
                            
                            <div class="settings-form">
                                <div class="form-group">
                                    <label>Date Selection *</label>
                                    <div class="date-selection">
                                        <label class="radio-label">
                                            <input type="radio" name="reminderDate" value="today" checked onchange="toggleDatePicker()">
                                            <span>Today</span>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="reminderDate" value="custom" onchange="toggleDatePicker()">
                                            <span>From Date</span>
                                        </label>
                                    </div>
                                    <input type="date" id="reminderCustomDate" class="form-control mt-2" style="display: none;">
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="reminderFromTime">From Time *</label>
                                            <select id="reminderFromTime" class="form-select" required>
                                                <option value="">Select time</option>
                                                @for($hour = 0; $hour < 24; $hour++)
                                                    @foreach([0, 30] as $minute)
                                                        @php
                                                            $timeValue = sprintf('%02d:%02d', $hour, $minute);
                                                            // Convert to 12-hour format with AM/PM
                                                            $displayHour = $hour == 0 ? 12 : ($hour > 12 ? $hour - 12 : $hour);
                                                            $amPm = $hour < 12 ? 'AM' : 'PM';
                                                            $timeDisplay = sprintf('%d:%02d %s', $displayHour, $minute, $amPm);
                                                        @endphp
                                                        <option value="{{ $timeValue }}">{{ $timeDisplay }}</option>
                                                    @endforeach
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="reminderToTime">To Time *</label>
                                            <select id="reminderToTime" class="form-select" required>
                                                <option value="">Select time</option>
                                                @for($hour = 0; $hour < 24; $hour++)
                                                    @foreach([0, 30] as $minute)
                                                        @php
                                                            $timeValue = sprintf('%02d:%02d', $hour, $minute);
                                                            // Convert to 12-hour format with AM/PM
                                                            $displayHour = $hour == 0 ? 12 : ($hour > 12 ? $hour - 12 : $hour);
                                                            $amPm = $hour < 12 ? 'AM' : 'PM';
                                                            $timeDisplay = sprintf('%d:%02d %s', $displayHour, $minute, $amPm);
                                                        @endphp
                                                        <option value="{{ $timeValue }}">{{ $timeDisplay }}</option>
                                                    @endforeach
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn-send-whatsapp" onclick="sendWhatsAppReminder()">
                                        <i class="fab fa-whatsapp"></i> Send WhatsApp Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Timetable Modal -->
    <div id="createTimetableModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-plus"></i> Create New Timetable</h3>
                <button class="modal-close" onclick="closeCreateModal()">&times;</button>
            </div>
            <div class="modal-body" id="createModalBody">
                <div class="modal-loading" id="createModalLoading">
                    <div class="spinner"></div>
                    <p>Loading form...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Timetable Modal -->
    <div id="editTimetableModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Timetable</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body" id="editModalBody">
                <div class="modal-loading" id="editModalLoading">
                    <div class="spinner"></div>
                    <p>Loading form...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 id="confirmModalTitle"><i class="fas fa-exclamation-triangle"></i> Confirm Action</h3>
                <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage">Are you sure you want to perform this action?</p>
            </div>
            <div class="modal-footer-buttons" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn-confirm" id="confirmModalBtn">
                    <span id="confirmModalBtnText">Confirm</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-check"></i> Event Details</h3>
                <button class="modal-close" onclick="closeEventModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Event details will be inserted here -->
            </div>
            <div class="modal-footer-buttons" id="eventModalFooter" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef; gap: 10px;">
                <!-- Action buttons will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Create Single Event Modal -->
    <div id="createEventModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-plus"></i> Create Event</h3>
                <button class="modal-close" onclick="closeCreateEventModal()">&times;</button>
            </div>
            <div class="modal-body" id="createEventModalBody">
                <!-- Form will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Reschedule Event Modal -->
    <div id="rescheduleEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-alt"></i> Reschedule Event</h3>
                <button class="modal-close" onclick="closeRescheduleModal()">&times;</button>
            </div>
            <div class="modal-body" id="rescheduleModalBody">
                <!-- Form will be inserted here -->
            </div>
        </div>
    </div>

    <!-- WhatsApp Sending Progress Modal -->
    <div id="whatsappProgressModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-body" style="text-align: center; padding: 40px 20px;">
                <div class="modal-loading">
                    <div class="spinner"></div>
                </div>
                <p style="margin-top: 20px; font-size: 16px; color: #495057; font-weight: 500;">
                    <i class="fab fa-whatsapp" style="color: #25D366; margin-right: 8px;"></i>
                    Sending WhatsApp Report...
                </p>
                <p style="margin-top: 10px; font-size: 14px; color: #6c757d;">
                    Please wait while we send the message
                </p>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let calendar;
        const isMobile = window.innerWidth <= 768;

        // Refresh Calendar Function
        function refreshCalendar() {
            if (calendar) {
                calendar.refetchEvents();
                // Add visual feedback
                const refreshBtn = document.querySelector('.topbar-actions button[onclick="refreshCalendar()"]');
                if (refreshBtn) {
                    const icon = refreshBtn.querySelector('i');
                    if (icon) {
                        icon.classList.add('fa-spin');
                        setTimeout(() => {
                            icon.classList.remove('fa-spin');
                        }, 1000);
                    }
                }
            }
        }

        // Toggle Filters Function
        function toggleFilters() {
            const filtersSection = document.getElementById('filtersSection');
            const filtersToggle = document.getElementById('filtersToggle');
            
            filtersSection.classList.toggle('collapsed');
            filtersToggle.classList.toggle('rotated');
        }

        // Check if filters have values (for reference only, doesn't auto-open)
        function checkFiltersState() {
            const filtersSection = document.getElementById('filtersSection');
            const filtersToggle = document.getElementById('filtersToggle');
            
            if (!filtersSection || !filtersToggle) return;
            
            // Always keep it collapsed by default - user must manually open it
            // Don't auto-open based on URL parameters
            filtersSection.classList.add('collapsed');
            filtersToggle.classList.remove('rotated');
        }

        // Initialize FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            // Clear default date values if they're not in URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const fromDateInput = document.getElementById('fromDate');
            const toDateInput = document.getElementById('toDate');
            
            if (fromDateInput && !urlParams.has('from_date')) {
                fromDateInput.value = '';
            }
            if (toDateInput && !urlParams.has('to_date')) {
                toDateInput.value = '';
            }
            
            // Ensure filters section is collapsed by default
            const filtersSection = document.getElementById('filtersSection');
            const filtersToggle = document.getElementById('filtersToggle');
            if (filtersSection && filtersToggle) {
                // Check if filters have values - only open if they do
                setTimeout(function() {
                    checkFiltersState();
                }, 100);
            }
            
            const calendarEl = document.getElementById('calendar');
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: isMobile ? 'timeGridDay' : 'timeGridDay',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: isMobile ? 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' : 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    showLoading();
                    
                    const params = new URLSearchParams();
                    
                    // Get filter values
                    const studentId = document.getElementById('studentFilter').value;
                    const teacherId = document.getElementById('teacherFilter').value;
                    const fromDate = document.getElementById('fromDate').value;
                    const toDate = document.getElementById('toDate').value;
                    
                    if (studentId) params.append('student_id', studentId);
                    if (teacherId) params.append('teacher_id', teacherId);
                    if (fromDate) params.append('from_date', fromDate);
                    if (toDate) params.append('to_date', toDate);
                    
                    // Add calendar date range
                    params.append('from_date', fetchInfo.startStr.split('T')[0]);
                    params.append('to_date', fetchInfo.endStr.split('T')[0]);
                    
                    fetch(`{{ route('timetable.events') }}?${params.toString()}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading();
                            successCallback(data);
                        })
                        .catch(error => {
                            hideLoading();
                            console.error('Error fetching events:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    showEventDetails(info.event);
                },
                dateClick: function(info) {
                    const dateStr = info.dateStr || info.date.toISOString().split('T')[0];
                    const timeStr = info.allDay ? null : (info.date.toISOString() || info.dateStr);
                    openCreateEventModal(dateStr, timeStr);
                },
                eventDisplay: 'block',
                height: 'auto',
                contentHeight: 'auto',
                dayMaxEvents: true,
                moreLinkClick: 'popover',
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                },
                slotMinTime: '00:00:00',
                slotMaxTime: '24:00:00',
                firstDay: 1, // Monday
                locale: 'en',
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day',
                    list: 'List'
                },
                // Mobile optimizations
                ...(isMobile && {
                    height: 'auto',
                    aspectRatio: 1.35,
                    eventMinHeight: 44,
                    slotLabelInterval: '01:00:00',
                    slotMinWidth: 60,
                })
            });

            calendar.render();

            // Handle filter form submission
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateURLFromFilters();
                calendar.refetchEvents();
                // Keep filters section open after applying filters
                // Don't call checkFiltersState() here - let user decide when to close
            });

            // Quick filter buttons
            document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.dataset.filter;
                    applyQuickFilter(filter);
                });
            });

            // Close modal on backdrop click
            document.getElementById('eventModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeEventModal();
                }
            });

            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    calendar.updateSize();
                }, 250);
            });
        });

        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        let currentEventId = null;
        let currentTimetableId = null;

        function showEventDetails(event) {
            const extendedProps = event.extendedProps;
            const modalBody = document.getElementById('modalBody');
            const modalFooter = document.getElementById('eventModalFooter');
            
            currentEventId = event.id;
            currentTimetableId = extendedProps.timetable_id || null;
            
            const statusClass = `status-${extendedProps.status || 'scheduled'}`;
            const statusText = (extendedProps.status || 'scheduled').charAt(0).toUpperCase() + 
                             (extendedProps.status || 'scheduled').slice(1);
            
            modalBody.innerHTML = `
                <div class="event-detail-item">
                    <div class="event-detail-label">Course Name</div>
                    <div class="event-detail-value">${extendedProps.course_name || event.title}</div>
                </div>
                <div class="event-detail-item">
                    <div class="event-detail-label">Student</div>
                    <div class="event-detail-value">${extendedProps.student_name || 'N/A'}</div>
                </div>
                <div class="event-detail-item">
                    <div class="event-detail-label">Teacher</div>
                    <div class="event-detail-value">${extendedProps.teacher_name || 'N/A'}</div>
                </div>
                <div class="event-detail-item">
                    <div class="event-detail-label">Date</div>
                    <div class="event-detail-value">${formatDate(extendedProps.event_date || event.startStr)}</div>
                </div>
                <div class="event-detail-item">
                    <div class="event-detail-label">Time</div>
                    <div class="event-detail-value">${extendedProps.start_time || ''} - ${extendedProps.end_time || ''}</div>
                </div>
                <div class="event-detail-item">
                    <div class="event-detail-label">Status</div>
                    <div class="event-detail-value">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                </div>
                ${extendedProps.notes ? `
                <div class="event-detail-item">
                    <div class="event-detail-label">Notes</div>
                    <div class="event-detail-value">${extendedProps.notes}</div>
                </div>
                ` : ''}
            `;
            
            modalFooter.innerHTML = `
                <button type="button" class="btn-action btn-edit" onclick="openRescheduleModal(${currentEventId})" style="flex: 1;">
                    <i class="fas fa-calendar-alt"></i> Reschedule
                </button>
                <button type="button" class="btn-action btn-delete" onclick="showDeleteEventOptions(${currentEventId}, ${currentTimetableId})" style="flex: 1;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            `;
            modalFooter.style.display = 'flex';
            
            document.getElementById('eventModal').classList.add('show');
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.remove('show');
            currentEventId = null;
            currentTimetableId = null;
        }

        // Create Single Event Modal Functions
        function openCreateEventModal(dateStr, timeStr) {
            const modal = document.getElementById('createEventModal');
            const modalBody = document.getElementById('createEventModalBody');
            
            modal.classList.add('show');
            modalBody.innerHTML = '<div class="modal-loading"><div class="spinner"></div><p>Loading form...</p></div>';
            
            const date = new Date(dateStr);
            const dateValue = date.toISOString().split('T')[0];
            const timeValue = timeStr ? (timeStr.split('T')[1] ? timeStr.split('T')[1].substring(0, 5) : '09:00') : '09:00';
            
            // Get timetables from the page data
            const timetablesData = @json($timetables->items() ?? []);
            
            // Ensure it's an array
            const timetables = Array.isArray(timetablesData) ? timetablesData : [];
            
            let timetableOptions = '<option value="">Select a timetable...</option>';
            if (timetables && timetables.length > 0) {
                timetables.forEach(function(timetable) {
                    const studentName = (timetable.student && timetable.student.name) ? timetable.student.name : 'N/A';
                    const startTime = timetable.start_time ? timetable.start_time.substring(0, 5) : '';
                    const endTime = timetable.end_time ? timetable.end_time.substring(0, 5) : '';
                    timetableOptions += '<option value="' + timetable.id + '" ' +
                        'data-start-time="' + startTime + '" ' +
                        'data-end-time="' + endTime + '">' +
                        (timetable.course_name || 'Course') + ' - ' + studentName +
                        '</option>';
                });
            }
            
            // Get students and teachers for dropdowns
            const students = @json($students ?? []);
            const teachers = @json($teachers ?? []);
            
            let studentOptions = '<option value="">Select a student...</option>';
            if (students && students.length > 0) {
                students.forEach(function(student) {
                    studentOptions += '<option value="' + student.id + '">' + (student.name || 'N/A') + '</option>';
                });
            }
            
            let teacherOptions = '<option value="">Select a teacher...</option>';
            if (teachers && teachers.length > 0) {
                teachers.forEach(function(teacher) {
                    teacherOptions += '<option value="' + teacher.id + '">' + (teacher.name || 'N/A') + '</option>';
                });
            }
            
            modalBody.innerHTML = 
                '<form id="createEventForm" onsubmit="submitCreateEvent(event)">' +
                    '<input type="hidden" name="event_date" value="' + dateValue + '">' +
                    '<input type="hidden" name="recurrence" value="single" id="recurrenceInput">' +
                    '<div class="form-group">' +
                        '<label for="eventStudentId">Student *</label>' +
                        '<select name="student_id" id="eventStudentId" class="form-control" required>' +
                            studentOptions +
                        '</select>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label for="eventTeacherId">Teacher *</label>' +
                        '<select name="teacher_id" id="eventTeacherId" class="form-control" required>' +
                            teacherOptions +
                        '</select>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label for="eventCourseName">Course Name *</label>' +
                        '<input type="text" name="course_name" id="eventCourseName" class="form-control" placeholder="Enter course name" required>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label for="eventStartTime">Start Time *</label>' +
                        '<input type="time" name="start_time" id="eventStartTime" class="form-control" value="' + timeValue + '" required>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label for="eventEndTime">End Time *</label>' +
                        '<input type="time" name="end_time" id="eventEndTime" class="form-control" required>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>Create For:</label>' +
                        '<div class="recurrence-options" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">' +
                            '<button type="button" class="recurrence-btn active" data-recurrence="single" onclick="selectRecurrence(\'single\', this)">' +
                                '<i class="fas fa-calendar-day"></i> This Day Only' +
                            '</button>' +
                            '<button type="button" class="recurrence-btn" data-recurrence="week" onclick="selectRecurrence(\'week\', this)">' +
                                '<i class="fas fa-calendar-week"></i> This Week' +
                            '</button>' +
                            '<button type="button" class="recurrence-btn" data-recurrence="month" onclick="selectRecurrence(\'month\', this)">' +
                                '<i class="fas fa-calendar-alt"></i> This Month' +
                            '</button>' +
                            '<button type="button" class="recurrence-btn" data-recurrence="year" onclick="selectRecurrence(\'year\', this)">' +
                                '<i class="fas fa-calendar"></i> This Year' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="modal-footer-buttons" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">' +
                        '<button type="button" class="btn-cancel" onclick="closeCreateEventModal()">Cancel</button>' +
                        '<button type="submit" class="btn-submit-modal">' +
                            '<i class="fas fa-save"></i> Create Event' +
                        '</button>' +
                    '</div>' +
                '</form>';
        }

        function closeCreateEventModal() {
            document.getElementById('createEventModal').classList.remove('show');
        }

        function selectRecurrence(recurrence, button) {
            document.querySelectorAll('.recurrence-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            document.getElementById('recurrenceInput').value = recurrence;
        }

        function loadTimetableDetails(timetableId) {
            const option = document.querySelector('#eventTimetableId option[value="' + timetableId + '"]');
            if (option && timetableId) {
                const startTime = option.dataset.startTime || '';
                const endTime = option.dataset.endTime || '';
                if (startTime) {
                    document.getElementById('eventStartTime').value = startTime.substring(0, 5);
                }
                if (endTime) {
                    document.getElementById('eventEndTime').value = endTime.substring(0, 5);
                }
            }
        }

        function submitCreateEvent(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            
            const submitBtn = form.querySelector('.btn-submit-modal');
            const originalContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner" style="width: 16px; height: 16px; border-width: 2px; display: inline-block;"></div> <span>Creating...</span>';
            
            fetch('{{ route("timetable.events.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    closeCreateEventModal();
                    calendar.refetchEvents();
                } else {
                    alert(data.message || 'Failed to create event');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
            });
        }

        // Delete Event Options
        function showDeleteEventOptions(eventId, timetableId) {
            closeEventModal();
            showConfirmModal(
                'Delete Event',
                'Do you want to delete this event only, or all events from this timetable?',
                'Delete This Event Only',
                'danger',
                function() {
                    deleteSingleEvent(eventId);
                }
            );
            
            // Add option to delete all events
            setTimeout(() => {
                const modal = document.getElementById('confirmModal');
                const footer = modal.querySelector('.modal-footer-buttons');
                if (footer && timetableId) {
                    const deleteAllBtn = document.createElement('button');
                    deleteAllBtn.type = 'button';
                    deleteAllBtn.className = 'btn-confirm warning';
                    deleteAllBtn.style.marginLeft = '10px';
                    deleteAllBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Delete All Events';
                    deleteAllBtn.onclick = function() {
                        closeConfirmModal();
                        showConfirmModal(
                            'Delete All Events',
                            'Are you sure you want to delete ALL events from this timetable? This action cannot be undone.',
                            'Delete All',
                            'danger',
                            function() {
                                deleteAllEvents(timetableId);
                            }
                        );
                    };
                    footer.appendChild(deleteAllBtn);
                }
            }, 100);
        }

        function deleteSingleEvent(eventId) {
            fetch(`{{ url('timetable/events') }}/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    closeConfirmModal();
                    calendar.refetchEvents();
                } else {
                    showErrorMessage(data.message || 'Failed to delete event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('An error occurred. Please try again.');
            });
        }

        function deleteAllEvents(timetableId) {
            fetch(`{{ url('timetable') }}/${timetableId}/events`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    closeConfirmModal();
                    calendar.refetchEvents();
                } else {
                    showErrorMessage(data.message || 'Failed to delete events');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('An error occurred. Please try again.');
            });
        }

        // Reschedule Event Modal Functions
        function openRescheduleModal(eventId) {
            closeEventModal();
            const modal = document.getElementById('rescheduleEventModal');
            const modalBody = document.getElementById('rescheduleModalBody');
            
            // Get event details first
            fetch(`{{ url('timetable/events') }}?date=${new Date().toISOString().split('T')[0]}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(events => {
                const event = events.find(e => e.id == eventId);
                if (!event) {
                    alert('Event not found');
                    return;
                }
                
                const eventDate = event.event_date || event.start.split('T')[0];
                const startTime = event.start_time || event.start.split('T')[1]?.substring(0, 5) || '';
                const endTime = event.end_time || event.end.split('T')[1]?.substring(0, 5) || '';
                
                modalBody.innerHTML = `
                    <form id="rescheduleEventForm" onsubmit="submitRescheduleEvent(event, ${eventId})">
                        <div class="form-group">
                            <label for="rescheduleDate">Event Date *</label>
                            <input type="date" name="event_date" id="rescheduleDate" class="form-control" value="${eventDate}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="rescheduleStartTime">Start Time *</label>
                            <input type="time" name="start_time" id="rescheduleStartTime" class="form-control" value="${startTime}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="rescheduleEndTime">End Time *</label>
                            <input type="time" name="end_time" id="rescheduleEndTime" class="form-control" value="${endTime}" required>
                        </div>
                        
                        <div class="modal-footer-buttons" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                            <button type="button" class="btn-cancel" onclick="closeRescheduleModal()">Cancel</button>
                            <button type="submit" class="btn-submit-modal">
                                <i class="fas fa-save"></i> Reschedule
                            </button>
                        </div>
                    </form>
                `;
                
                modal.classList.add('show');
            })
            .catch(error => {
                console.error('Error loading event:', error);
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load event. Please try again.</div>';
            });
        }

        function closeRescheduleModal() {
            document.getElementById('rescheduleEventModal').classList.remove('show');
        }

        function submitRescheduleEvent(e, eventId) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            
            const submitBtn = form.querySelector('.btn-submit-modal');
            const originalContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner" style="width: 16px; height: 16px; border-width: 2px; display: inline-block;"></div> <span>Rescheduling...</span>';
            
            fetch(`{{ url('timetable/events') }}/${eventId}/reschedule`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    closeRescheduleModal();
                    calendar.refetchEvents();
                } else {
                    alert(data.message || 'Failed to reschedule event');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
            });
        }

        // Close modals on backdrop click (except progress modal)
        document.getElementById('createEventModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateEventModal();
            }
        });

        // Prevent progress modal from closing on backdrop click
        document.getElementById('whatsappProgressModal').addEventListener('click', function(e) {
            if (e.target === this) {
                // Don't close - prevent default behavior
                e.stopPropagation();
            }
        });

        document.getElementById('rescheduleEventModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRescheduleModal();
            }
        });

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }

        // Apply calendar filters immediately when dropdowns change
        function applyCalendarFilters() {
            if (typeof calendar !== 'undefined' && calendar) {
                updateURLFromFilters();
                calendar.refetchEvents();
            }
        }

        function updateURLFromFilters() {
            const params = new URLSearchParams();
            const studentId = document.getElementById('studentFilter')?.value || '';
            const teacherId = document.getElementById('teacherFilter')?.value || '';
            const fromDate = document.getElementById('fromDate')?.value || '';
            const toDate = document.getElementById('toDate')?.value || '';
            
            if (studentId) params.append('student_id', studentId);
            if (teacherId) params.append('teacher_id', teacherId);
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);
            
            const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.pushState({}, '', newURL);
        }

        function applyQuickFilter(filter) {
            const today = new Date();
            const fromDateInput = document.getElementById('fromDate');
            const toDateInput = document.getElementById('toDate');
            
            // Remove active class from all buttons
            document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            if (filter === 'today') {
                const todayStr = today.toISOString().split('T')[0];
                fromDateInput.value = todayStr;
                toDateInput.value = todayStr;
                document.querySelector('[data-filter="today"]').classList.add('active');
            } else if (filter === 'week') {
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Monday
                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6); // Sunday
                
                fromDateInput.value = startOfWeek.toISOString().split('T')[0];
                toDateInput.value = endOfWeek.toISOString().split('T')[0];
                document.querySelector('[data-filter="week"]').classList.add('active');
            } else if (filter === 'month') {
                const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                
                fromDateInput.value = startOfMonth.toISOString().split('T')[0];
                toDateInput.value = endOfMonth.toISOString().split('T')[0];
                document.querySelector('[data-filter="month"]').classList.add('active');
            } else if (filter === 'clear') {
                fromDateInput.value = '';
                toDateInput.value = '';
                document.getElementById('studentFilter').value = '';
                document.getElementById('teacherFilter').value = '';
            }
            
            updateURLFromFilters();
            calendar.refetchEvents();
        }

        // Handle escape key to close modal (but not progress modal)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const progressModal = document.getElementById('whatsappProgressModal');
                // Don't close if progress modal is showing
                if (!progressModal.classList.contains('show')) {
                    closeEventModal();
                }
            }
        });

        // Handle sidebar navigation
        document.querySelectorAll('.sidebar-btn[data-view]').forEach(btn => {
            btn.addEventListener('click', function() {
                const view = this.dataset.view;
                
                // Update active button
                document.querySelectorAll('.sidebar-btn').forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');
                
                // Show/hide views
                document.querySelectorAll('.view-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                if (view === 'calendar') {
                    document.getElementById('calendarView').classList.add('active');
                    // Refresh calendar if needed
                    if (calendar) {
                        calendar.updateSize();
                    }
                } else if (view === 'timetables') {
                    document.getElementById('timetablesView').classList.add('active');
                } else if (view === 'settings') {
                    document.getElementById('settingsView').classList.add('active');
                }

                // Close sidebar on mobile after selection
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const toggleIcon = document.getElementById('toggleIcon');

        function openSidebar() {
            sidebar.classList.add('show');
            sidebarBackdrop.classList.add('show');
            sidebarToggle.classList.add('active', 'hide');
            toggleIcon.classList.remove('fa-bars');
            toggleIcon.classList.add('fa-times');
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            sidebarBackdrop.classList.remove('show');
            sidebarToggle.classList.remove('active', 'hide');
            toggleIcon.classList.remove('fa-times');
            toggleIcon.classList.add('fa-bars');
        }

        // Toggle button click
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        // Close on backdrop click
        sidebarBackdrop.addEventListener('click', closeSidebar);

        // Swipe gesture disabled - sidebar only opens via menu icon click
        // Removed swipe-to-open functionality as per user request

        // Close sidebar when clicking on backdrop or outside on mobile (only when sidebar is open)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
                // Only close if clicking on backdrop or outside sidebar (not on sidebar itself or toggle button)
                const isClickOnBackdrop = e.target === sidebarBackdrop;
                const isClickOutside = !sidebar.contains(e.target) && !sidebarToggle.contains(e.target);
                
                if (isClickOnBackdrop || isClickOutside) {
                    closeSidebar();
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                // Always show sidebar on desktop
                sidebar.classList.add('show');
                sidebarBackdrop.classList.remove('show');
            } else {
                // Hide sidebar by default on mobile
                closeSidebar();
            }
        });

        // Initialize: hide sidebar on mobile on page load
        if (window.innerWidth <= 768) {
            closeSidebar();
        }

        // Create Timetable Modal Functions
        function openCreateModal() {
            const modal = document.getElementById('createTimetableModal');
            const modalBody = document.getElementById('createModalBody');
            const loading = document.getElementById('createModalLoading');
            
            modal.classList.add('show');
            modalBody.innerHTML = '<div class="modal-loading" id="createModalLoading"><div class="spinner"></div><p>Loading form...</p></div>';
            
            // Load form via AJAX
            fetch('{{ route("timetable.create") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
                attachFormHandlers();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load form. Please try again.</div>';
            });

            // Close sidebar if open on mobile
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        }

        function closeCreateModal() {
            document.getElementById('createTimetableModal').classList.remove('show');
        }

        function attachFormHandlers() {
            const form = document.getElementById('timetableFormModal');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate days of week
                const checkedDays = form.querySelectorAll('input[name="days_of_week[]"]:checked');
                if (checkedDays.length === 0) {
                    alert('Please select at least one day of the week.');
                    return false;
                }

                // Validate end time is after start time
                const startTime = form.querySelector('input[name="start_time"]').value;
                const endTime = form.querySelector('input[name="end_time"]').value;
                if (startTime && endTime && endTime <= startTime) {
                    alert('End time must be after start time.');
                    return false;
                }

                // Validate end date is after or equal to start date
                const startDate = form.querySelector('input[name="start_date"]').value;
                const endDate = form.querySelector('input[name="end_date"]').value;
                if (startDate && endDate && endDate < startDate) {
                    alert('End date must be after or equal to start date.');
                    return false;
                }

                // Show loading spinner
                const submitBtn = form.querySelector('.btn-submit-modal');
                const originalContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="spinner" style="width: 16px; height: 16px; border-width: 2px; display: inline-block;"></div> <span>Saving...</span>';

                // Get form data
                const formData = new FormData(form);

                // Submit via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw { data, status: response.status };
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showSuccessMessage(data.message);
                        
                        // Close modal
                        closeCreateModal();
                        
                        // Refresh calendar if on calendar view
                        if (calendar && document.getElementById('calendarView').classList.contains('active')) {
                            calendar.refetchEvents();
                        }
                        
                        // Refresh timetables list if on list view
                        if (document.getElementById('timetablesView').classList.contains('active')) {
                            location.reload();
                        }
                    } else {
                        // Show error
                        alert(data.message || 'Failed to create timetable');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Handle validation errors
                    if (error.data && error.data.errors) {
                        let errorHtml = '<div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle"></i><strong>Please fix the following errors:</strong><ul class="mb-0 mt-2">';
                        Object.keys(error.data.errors).forEach(key => {
                            error.data.errors[key].forEach(msg => {
                                errorHtml += `<li>${msg}</li>`;
                            });
                        });
                        errorHtml += '</ul></div>';
                        
                        // Insert error at top of form
                        const form = document.getElementById('timetableFormModal');
                        const existingError = form.querySelector('.alert-danger');
                        if (existingError) {
                            existingError.remove();
                        }
                        form.insertAdjacentHTML('afterbegin', errorHtml);
                    } else {
                        alert(error.data?.message || 'An error occurred. Please try again.');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                });
            });
        }

        function showSuccessMessage(message) {
            // Create success alert
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check-circle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert at the top of content area
            const contentArea = document.querySelector('.content-area');
            contentArea.insertBefore(alert, contentArea.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Close create modal on backdrop click
        document.getElementById('createTimetableModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });

        // Edit Timetable Modal Functions
        function openEditModal(timetableId) {
            const modal = document.getElementById('editTimetableModal');
            const modalBody = document.getElementById('editModalBody');
            
            modal.classList.add('show');
            modalBody.innerHTML = '<div class="modal-loading" id="editModalLoading"><div class="spinner"></div><p>Loading form...</p></div>';
            
            // Load form via AJAX
            fetch(`{{ url('timetable') }}/${timetableId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
                attachEditFormHandlers(timetableId);
            })
            .catch(error => {
                console.error('Error loading form:', error);
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load form. Please try again.</div>';
            });

            // Close sidebar if open on mobile
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        }

        function closeEditModal() {
            document.getElementById('editTimetableModal').classList.remove('show');
        }

        function attachEditFormHandlers(timetableId) {
            const form = document.getElementById('timetableEditFormModal');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate days of week
                const checkedDays = form.querySelectorAll('input[name="days_of_week[]"]:checked');
                if (checkedDays.length === 0) {
                    alert('Please select at least one day of the week.');
                    return false;
                }

                // Validate end time is after start time
                const startTime = form.querySelector('input[name="start_time"]').value;
                const endTime = form.querySelector('input[name="end_time"]').value;
                if (startTime && endTime && endTime <= startTime) {
                    alert('End time must be after start time.');
                    return false;
                }

                // Validate end date is after or equal to start date
                const startDate = form.querySelector('input[name="start_date"]').value;
                const endDate = form.querySelector('input[name="end_date"]').value;
                if (startDate && endDate && endDate < startDate) {
                    alert('End date must be after or equal to start date.');
                    return false;
                }

                // Show loading spinner
                const submitBtn = form.querySelector('.btn-submit-modal');
                const originalContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="spinner" style="width: 16px; height: 16px; border-width: 2px; display: inline-block;"></div> <span>Updating...</span>';

                // Get form data
                const formData = new FormData(form);

                // Submit via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw { data, status: response.status };
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSuccessMessage(data.message);
                        closeEditModal();
                        // Refresh only the timetables list without full page reload
                        refreshTimetablesList();
                    } else {
                        alert(data.message || 'Failed to update timetable');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    if (error.data && error.data.errors) {
                        let errorHtml = '<div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle"></i><strong>Please fix the following errors:</strong><ul class="mb-0 mt-2">';
                        Object.keys(error.data.errors).forEach(key => {
                            error.data.errors[key].forEach(msg => {
                                errorHtml += `<li>${msg}</li>`;
                            });
                        });
                        errorHtml += '</ul></div>';
                        
                        const existingError = form.querySelector('.alert-danger');
                        if (existingError) {
                            existingError.remove();
                        }
                        form.insertAdjacentHTML('afterbegin', errorHtml);
                    } else {
                        alert(error.data?.message || 'An error occurred. Please try again.');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                });
            });
        }

        // Delete Timetable Function
        function deleteTimetable(timetableId) {
            showConfirmModal(
                'Delete Timetable',
                'Are you sure you want to delete this timetable? This will also delete all associated events. This action cannot be undone.',
                'Delete',
                'danger',
                function() {
                    fetch(`{{ url('timetable') }}/${timetableId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage(data.message);
                            refreshTimetablesList();
                        } else {
                            showErrorMessage(data.message || 'Failed to delete timetable');
                        }
                        closeConfirmModal();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('An error occurred. Please try again.');
                        closeConfirmModal();
                    });
                }
            );
        }

        // Toggle Timetable Status Function
        function toggleTimetableStatus(timetableId, currentStatus) {
            const isActive = !currentStatus;
            const action = isActive ? 'activate' : 'deactivate';
            const actionText = isActive ? 'Activate' : 'Deactivate';
            
            showConfirmModal(
                `${actionText} Timetable`,
                `Are you sure you want to ${action} this timetable?`,
                actionText,
                isActive ? 'success' : 'warning',
                function() {
                    fetch(`{{ url('timetable') }}/${timetableId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage(data.message);
                            refreshTimetablesList();
                        } else {
                            showErrorMessage(data.message || 'Failed to update timetable status');
                        }
                        closeConfirmModal();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('An error occurred. Please try again.');
                        closeConfirmModal();
                    });
                }
            );
        }

        // Refresh Timetables List Function (AJAX reload)
        function refreshTimetablesList() {
            // Only refresh if we're on the timetables view
            if (!document.getElementById('timetablesView').classList.contains('active')) {
                return;
            }

            // Show loading state
            const listContent = document.getElementById('timetablesListContent');
            if (!listContent) return;
            
            // Preserve search term
            const searchTerm = document.getElementById('timetableSearch')?.value || '';
            
            const originalContent = listContent.innerHTML;
            listContent.innerHTML = '<div class="modal-loading" style="padding: 40px;"><div class="spinner"></div><p>Refreshing...</p></div>';

            // Fetch only the timetables list section via AJAX
            fetch('{{ route("timetable.index") }}?section=timetables', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                listContent.innerHTML = html;
                
                // Restore search if it was active
                if (searchTerm) {
                    document.getElementById('timetableSearch').value = searchTerm;
                    document.getElementById('searchClear').classList.add('show');
                    filterTimetables();
                }
            })
            .catch(error => {
                console.error('Error refreshing list:', error);
                // Fallback to full page reload on error
                location.reload();
            });
        }

        // Close edit modal on backdrop click
        document.getElementById('editTimetableModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Confirmation Modal Functions
        let confirmCallback = null;

        function showConfirmModal(title, message, buttonText, type, callback) {
            const modal = document.getElementById('confirmModal');
            const modalTitle = document.getElementById('confirmModalTitle');
            const modalMessage = document.getElementById('confirmModalMessage');
            const modalBtn = document.getElementById('confirmModalBtn');
            const modalBtnText = document.getElementById('confirmModalBtnText');
            
            modalTitle.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${title}`;
            modalMessage.textContent = message;
            modalBtnText.textContent = buttonText;
            
            // Set button style based on type
            modalBtn.className = 'btn-confirm';
            if (type === 'warning') {
                modalBtn.classList.add('warning');
            } else if (type === 'success') {
                modalBtn.classList.add('success');
            }
            
            confirmCallback = callback;
            modal.classList.add('show');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('show');
            confirmCallback = null;
        }

        // Handle confirm button click
        document.getElementById('confirmModalBtn').addEventListener('click', function() {
            if (confirmCallback) {
                confirmCallback();
            }
        });

        // Close confirm modal on backdrop click
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        // Search Functionality
        function filterTimetables() {
            const searchTerm = document.getElementById('timetableSearch').value.toLowerCase().trim();
            const studentFilter = document.getElementById('timetableStudentFilter')?.value || '';
            const teacherFilter = document.getElementById('timetableTeacherFilter')?.value || '';
            const searchClear = document.getElementById('searchClear');
            const timetableCards = document.querySelectorAll('.timetable-card');
            
            // Show/hide clear button
            if (searchTerm) {
                searchClear.classList.add('show');
            } else {
                searchClear.classList.remove('show');
            }
            
            // Filter timetable cards
            let visibleCount = 0;
            timetableCards.forEach(card => {
                const courseName = (card.querySelector('.timetable-title')?.textContent || '').toLowerCase();
                const studentName = (card.querySelector('.info-item:nth-child(1) .info-value')?.textContent || '').toLowerCase();
                const teacherName = (card.querySelector('.info-item:nth-child(2) .info-value')?.textContent || '').toLowerCase();
                
                // Get data attributes for filtering
                const cardStudentId = card.getAttribute('data-student-id') || '';
                const cardTeacherId = card.getAttribute('data-teacher-id') || '';
                
                // Check search term match
                const searchMatch = !searchTerm || 
                                   courseName.includes(searchTerm) || 
                                   studentName.includes(searchTerm) || 
                                   teacherName.includes(searchTerm);
                
                // Check student filter match
                const studentMatch = !studentFilter || cardStudentId === studentFilter;
                
                // Check teacher filter match
                const teacherMatch = !teacherFilter || cardTeacherId === teacherFilter;
                
                const matches = searchMatch && studentMatch && teacherMatch;
                
                if (matches) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show empty state if no results
            const listContent = document.getElementById('timetablesListContent');
            let emptyState = listContent.querySelector('.search-empty-state');
            
            const hasActiveFilters = searchTerm || studentFilter || teacherFilter;
            
            if (hasActiveFilters && visibleCount === 0) {
                if (!emptyState) {
                    emptyState = document.createElement('div');
                    emptyState.className = 'empty-state search-empty-state';
                    emptyState.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h3>No Results Found</h3>
                        <p>No timetables match your current filters</p>
                    `;
                    listContent.appendChild(emptyState);
                }
                emptyState.style.display = 'block';
            } else if (emptyState) {
                emptyState.style.display = 'none';
            }
        }

        function clearSearch() {
            document.getElementById('timetableSearch').value = '';
            document.getElementById('searchClear').classList.remove('show');
            filterTimetables();
        }

        function showErrorMessage(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-exclamation-circle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const contentArea = document.querySelector('.content-area');
            contentArea.insertBefore(alert, contentArea.firstChild);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Settings Functions
        function adjustHours(change) {
            const input = document.getElementById('hourAdjustment');
            let currentValue = parseInt(input.value) || 0;
            let newValue = currentValue + change;
            
            // Clamp between -23 and 23
            newValue = Math.max(-23, Math.min(23, newValue));
            input.value = newValue;
            
            // Check affected count when country is selected
            const country = document.getElementById('timezoneCountry').value;
            if (country) {
                checkAffectedCount(country);
            }
        }

        function toggleDatePicker() {
            const customDateRadio = document.querySelector('input[name="reminderDate"][value="custom"]');
            const customDateInput = document.getElementById('reminderCustomDate');
            
            if (customDateRadio.checked) {
                customDateInput.style.display = 'block';
                customDateInput.required = true;
            } else {
                customDateInput.style.display = 'none';
                customDateInput.required = false;
                customDateInput.value = '';
            }
        }

        function checkAffectedCount(country) {
            if (!country) {
                document.getElementById('timezoneAffectedCount').style.display = 'none';
                return;
            }

            fetch(`{{ route('timetable.index') }}?check_country=${encodeURIComponent(country)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const count = data.count || 0;
                const countElement = document.getElementById('timezoneAffectedCount');
                const countText = document.getElementById('affectedCountText');
                
                if (count > 0) {
                    countText.textContent = `${count} timetable(s) will be affected`;
                    countElement.style.display = 'flex';
                } else {
                    countElement.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error checking affected count:', error);
            });
        }

        // Listen for country change
        document.getElementById('timezoneCountry').addEventListener('change', function() {
            checkAffectedCount(this.value);
        });

        function applyTimezoneAdjustment() {
            const country = document.getElementById('timezoneCountry').value;
            const hours = parseInt(document.getElementById('hourAdjustment').value) || 0;

            if (!country) {
                alert('Please select a country');
                return;
            }

            if (hours === 0) {
                alert('Please enter a non-zero hour adjustment');
                return;
            }

            if (!confirm(`Are you sure you want to ${hours > 0 ? 'add' : 'subtract'} ${Math.abs(hours)} hour(s) to all timetables in ${country}? This action cannot be undone.`)) {
                return;
            }

            const btn = document.querySelector('.btn-apply-timezone');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner" style="width: 16px; height: 16px; border-width: 2px; display: inline-block;"></div> <span>Applying...</span>';

            fetch('{{ route("timetable.adjust-timezone") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    country: country,
                    hours: hours
                })
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                
                if (data.success) {
                    showSuccessMessage(data.message || 'Timezone adjustment applied successfully!');
                    // Reset form
                    document.getElementById('timezoneCountry').value = '';
                    document.getElementById('hourAdjustment').value = '0';
                    document.getElementById('timezoneAffectedCount').style.display = 'none';
                    // Refresh calendar if it exists
                    if (calendar) {
                        calendar.refetchEvents();
                    }
                } else {
                    showErrorMessage(data.message || 'Failed to apply timezone adjustment');
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                console.error('Error:', error);
                showErrorMessage('An error occurred while applying timezone adjustment');
            });
        }

        function sendWhatsAppReminder() {
            const dateRadio = document.querySelector('input[name="reminderDate"]:checked');
            const customDate = document.getElementById('reminderCustomDate').value;
            const fromTime = document.getElementById('reminderFromTime').value;
            const toTime = document.getElementById('reminderToTime').value;

            if (!dateRadio) {
                alert('Please select a date option');
                return;
            }

            let selectedDate;
            if (dateRadio.value === 'today') {
                selectedDate = new Date().toISOString().split('T')[0];
            } else {
                if (!customDate) {
                    alert('Please select a date');
                    return;
                }
                selectedDate = customDate;
            }

            if (!fromTime || !toTime) {
                alert('Please select both from and to times');
                return;
            }

            const btn = document.querySelector('.btn-send-whatsapp');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner" style="width: 16px; height: 16px; border-width: 2px; display: inline-block;"></div> <span>Sending...</span>';

            // Show progress modal
            const progressModal = document.getElementById('whatsappProgressModal');
            progressModal.classList.add('show');

            fetch('{{ route("timetable.send-whatsapp-reminder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    date: selectedDate,
                    from_time: fromTime,
                    to_time: toTime
                })
            })
            .then(response => response.json())
            .then(data => {
                // Hide progress modal
                const progressModal = document.getElementById('whatsappProgressModal');
                progressModal.classList.remove('show');
                
                btn.disabled = false;
                btn.innerHTML = originalContent;
                
                if (data.success) {
                    showSuccessMessage(data.message || 'WhatsApp reminder sent successfully!');
                    // Reset form
                    document.querySelector('input[name="reminderDate"][value="today"]').checked = true;
                    document.getElementById('reminderCustomDate').value = '';
                    document.getElementById('reminderCustomDate').style.display = 'none';
                    document.getElementById('reminderFromTime').value = '';
                    document.getElementById('reminderToTime').value = '';
                } else {
                    showErrorMessage(data.message || 'Failed to send WhatsApp reminder');
                }
            })
            .catch(error => {
                // Hide progress modal
                const progressModal = document.getElementById('whatsappProgressModal');
                progressModal.classList.remove('show');
                
                btn.disabled = false;
                btn.innerHTML = originalContent;
                console.error('Error:', error);
                showErrorMessage('An error occurred while sending WhatsApp reminder');
            });
        }
    </script>
</body>
</html>

