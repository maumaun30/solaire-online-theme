import { useEffect, useState } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  SelectControl,
  RangeControl,
  ToggleControl,
  Spinner,
  Placeholder,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { sectionLabel, postType, postsPerPage, showLoadMore, columns } = attributes;

  const [posts, setPosts] = useState([]);
  const [postTypes, setPostTypes] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');
  const [siteLogo, setSiteLogo] = useState('');

  // Site logo — the fallback for posts with no featured image, mirroring
  // single.php. Falls through to the wordmark when none is set.
  useEffect(() => {
    apiFetch({ path: '/wp/v2/settings' })
      .then((settings) => {
        if (!settings?.site_logo) return;
        return apiFetch({ path: `/wp/v2/media/${settings.site_logo}` }).then((media) => {
          setSiteLogo(media?.source_url || '');
        });
      })
      .catch(() => {});
  }, []);

  // Available public post types — the attribute stores the REST base.
  useEffect(() => {
    apiFetch({ path: '/wp/v2/types' })
      .then((types) => {
        setPostTypes(
          Object.entries(types)
            .filter(([, t]) => t.viewable !== false && t.rest_base)
            .map(([, t]) => ({ label: t.name, value: t.rest_base }))
        );
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    setIsLoading(true);
    setError('');

    const restBase = postType || 'posts';

    apiFetch({
      path: `/wp/v2/${restBase}?per_page=${postsPerPage}&_embed=1&status=publish`,
    })
      .then((data) => {
        setPosts(Array.isArray(data) ? data : []);
        setIsLoading(false);
      })
      .catch(() => {
        setError(`Could not load posts for "${restBase}". Check the post type REST base.`);
        setPosts([]);
        setIsLoading(false);
      });
  }, [postType, postsPerPage]);

  const blockProps = useBlockProps({ className: 'solaire-post-archive' });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Archive Settings')} initialOpen={true}>
          <TextControl
            label={__('Section Label')}
            value={sectionLabel}
            onChange={(value) => setAttributes({ sectionLabel: value })}
          />
          <SelectControl
            label={__('Post Type')}
            value={postType}
            options={postTypes.length ? postTypes : [{ label: 'Posts', value: 'posts' }]}
            onChange={(value) => setAttributes({ postType: value })}
            help={__('Displays all public post types registered with REST API support.')}
          />
          <RangeControl
            label={__('Posts Per Page')}
            value={postsPerPage}
            onChange={(value) => setAttributes({ postsPerPage: value })}
            min={2}
            max={20}
          />
          <RangeControl
            label={__('Columns')}
            value={columns}
            onChange={(value) => setAttributes({ columns: value })}
            min={1}
            max={4}
          />
          <ToggleControl
            label={__('Show Load More Button')}
            checked={showLoadMore}
            onChange={(value) => setAttributes({ showLoadMore: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {sectionLabel && (
          <p className="solaire-post-archive__label">{sectionLabel}</p>
        )}

        {isLoading ? (
          <div style={{ padding: '40px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <Spinner />
            <span style={{ color: 'rgba(255,255,255,0.5)', fontSize: '13px' }}>Loading posts…</span>
          </div>
        ) : error ? (
          <Placeholder label={__('Post Archive')} instructions={error} />
        ) : posts.length === 0 ? (
          <Placeholder
            label={__('No posts found')}
            instructions={__('No published posts found for this post type. Try publishing some posts first.')}
          />
        ) : (
          <div
            className="solaire-post-archive__grid"
            style={{ '--archive-cols': columns }}
          >
            {posts.map((post) => {
              const thumb = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
              const title = post.title?.rendered || __('Untitled');
              const excerpt = post.excerpt?.rendered
                ? post.excerpt.rendered.replace(/<[^>]+>/g, '').substring(0, 80) + '…'
                : '';

              return (
                <article key={post.id} className="solaire-archive-card">
                  <div className="solaire-archive-card__thumb">
                    {thumb ? (
                      <img src={thumb} alt={title} />
                    ) : (
                      <div className="solaire-archive-card__thumb-placeholder">
                        {siteLogo ? (
                          <img
                            className="solaire-archive-card__thumb-logo"
                            src={siteLogo}
                            alt=""
                          />
                        ) : (
                          <span className="solaire-archive-card__wordmark">
                            <span className="solaire-archive-card__wordmark-main">SOLAIRE</span>
                            <span className="solaire-archive-card__wordmark-sub">ONLINE</span>
                          </span>
                        )}
                      </div>
                    )}
                    <div className="solaire-archive-card__overlay" />
                  </div>

                  <div className="solaire-archive-card__content">
                    <h3
                      className="solaire-archive-card__title"
                      dangerouslySetInnerHTML={{ __html: title }}
                    />
                    {excerpt && (
                      <p className="solaire-archive-card__excerpt">{excerpt}</p>
                    )}
                    <span className="solaire-archive-card__btn">{__('Read More')}</span>
                  </div>
                </article>
              );
            })}
          </div>
        )}

        {showLoadMore && !isLoading && (
          <div className="solaire-post-archive__load-more-wrap">
            <button className="solaire-post-archive__load-more" disabled>
              {__('Load More')}
            </button>
            <p className="solaire-post-archive__load-more-note">
              {__('(Load More is active on the front end)')}
            </p>
          </div>
        )}
      </div>
    </>
  );
}
