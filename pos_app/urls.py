from django.urls import path
from pos_app import views

urlpatterns = [
    # Web Views
    path('', views.pos_view, name='pos_home'),
    path('pos/', views.pos_view, name='pos_terminal'),
    path('analytics/', views.analytics_view, name='analytics_dashboard'),
    path('transactions/<int:transaction_id>/print/', views.print_receipt_view, name='print_receipt'),

    # REST APIs
    path('api/pos/scan/', views.api_scan, name='api_pos_scan'),
    path('api/pos/search/', views.api_search_products, name='api_pos_search'),
    path('api/pos/checkout/', views.api_checkout, name='api_pos_checkout'),
    path('api/analytics/summary/', views.api_duckdb_summary, name='api_duckdb_summary'),
]
